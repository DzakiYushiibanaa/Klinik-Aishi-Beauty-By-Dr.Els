<?php

namespace App\Filament\Pages;

use App\Exports\FinanceReportExport;
use App\Services\FinanceReportService;
use BackedEnum;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class FinanceReport extends Page
{
    protected string $view = 'filament.pages.finance-report';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Laporan Keuangan';

    protected static ?int $navigationSort = 89; // di atas laporan-laporan lain

    public ?int $filter_month = null;
    public ?int $filter_year = null;
    public ?int $filter_week = null;
    public ?string $filter_order_type = 'all';
    public ?string $filter_payment_method = 'all';
    public ?string $filter_payment_status = 'sudah_dibayar';
    public ?string $filter_search = '';
    public ?string $filter_sort_field = 'paid_at';
    public ?string $filter_sort_dir = 'desc';

    public int $per_page = 10;
    public int $page = 1;

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'reports/finance';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Laporan Keuangan';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Laporan Keuangan';
    }

    public function getSubheading(): ?string
    {
        return 'Ringkasan pemasukan klinik dari produk, treatment, konsultasi, dan transaksi sudah dibayar.';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) return true;
        return method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;
    }

    public function mount(): void
    {
        $this->filter_month = (int) now()->month;
        $this->filter_year = (int) now()->year;
    }

    public function updated($name): void
    {
        if (str_starts_with($name, 'filter_')) {
            $this->page = 1;
        }
    }

    public function nextPage(): void { $this->page++; }
    public function previousPage(): void { if ($this->page > 1) $this->page--; }
    public function gotoPage(int $page): void { $this->page = max(1, $page); }

    public function sortBy(string $field): void
    {
        if ($this->filter_sort_field === $field) {
            $this->filter_sort_dir = $this->filter_sort_dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->filter_sort_field = $field;
            $this->filter_sort_dir = 'desc';
        }
        $this->page = 1;
    }

    public function resetFilters(): void
    {
        $this->filter_month = (int) now()->month;
        $this->filter_year = (int) now()->year;
        $this->filter_week = null;
        $this->filter_order_type = 'all';
        $this->filter_payment_method = 'all';
        $this->filter_payment_status = 'sudah_dibayar';
        $this->filter_search = '';
        $this->filter_sort_field = 'paid_at';
        $this->filter_sort_dir = 'desc';
        $this->page = 1;
    }

    protected function service(): FinanceReportService
    {
        return new FinanceReportService([
            'month' => $this->filter_month,
            'year' => $this->filter_year,
            'week' => $this->filter_week,
            'order_type' => $this->filter_order_type,
            'payment_method' => $this->filter_payment_method,
            'payment_status' => $this->filter_payment_status,
            'search' => $this->filter_search,
            'sort_field' => $this->filter_sort_field,
            'sort_dir' => $this->filter_sort_dir,
        ]);
    }

    public function getViewDataProperty(): array
    {
        $service = $this->service();
        $rows = $service->tableRows();
        $totalRows = $rows->count();
        $perPage = max(5, (int) $this->per_page);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $this->page = min($this->page, $totalPages);
        $paged = $rows->forPage($this->page, $perPage)->values();

        return [
            'service' => $service,
            'summary' => $service->summary(),
            'rows' => $paged,
            'totalRows' => $totalRows,
            'totalPages' => $totalPages,
            'tableTotals' => $service->tableTotals(),
            'weeks' => $service->weeklyRecap(),
            'chartRevenueWeek' => $service->revenueByWeekChart(),
            'chartComposition' => $service->revenueCompositionChart(),
            'chartPaymentMethod' => $service->paymentMethodChart(),
            'periodLabel' => $service->periodLabel(),
            'monthOptions' => FinanceReportService::monthOptions(),
            'yearOptions' => FinanceReportService::yearOptions(),
            'weekOptions' => FinanceReportService::weekOptions(),
            'orderTypeOptions' => FinanceReportService::orderTypeOptions(),
            'paymentMethodOptions' => FinanceReportService::paymentMethodOptions(),
            'paymentStatusOptions' => FinanceReportService::paymentStatusOptions(),
        ];
    }

    public function exportExcel()
    {
        return Excel::download(new FinanceReportExport($this->service()), $this->buildFilename('xlsx'));
    }

    public function exportCsv()
    {
        return Excel::download(
            new \App\Exports\FinanceReportDetailSheet($this->service()),
            $this->buildFilename('csv'),
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf()
    {
        $service = $this->service();
        $data = [
            'service' => $service,
            'summary' => $service->summary(),
            'rows' => $service->tableRows(),
            'tableTotals' => $service->tableTotals(),
            'weeks' => $service->weeklyRecap(),
            'periodLabel' => $service->periodLabel(),
            'now' => now()->translatedFormat('d M Y H:i'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.finance-report', $data)
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->stream()),
            $this->buildFilename('pdf')
        );
    }

    protected function buildFilename(string $ext): string
    {
        $month = FinanceReportService::monthOptions()[$this->filter_month] ?? $this->filter_month;
        $base = sprintf('laporan-keuangan-%s-%s', strtolower($month), $this->filter_year);
        if ($this->filter_week) {
            $base .= '-minggu-' . $this->filter_week;
        }
        return "{$base}-" . now()->format('YmdHis') . ".{$ext}";
    }
}
