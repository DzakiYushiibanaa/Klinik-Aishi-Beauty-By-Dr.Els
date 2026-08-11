<?php

namespace App\Filament\Pages;

use App\Exports\TreatmentSalesReportExport;
use App\Services\TreatmentSalesReportService;
use BackedEnum;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class TreatmentSalesReport extends Page
{
    protected string $view = 'filament.pages.treatment-sales-report';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Laporan Treatment';

    protected static ?int $navigationSort = 91;

    /** Filter state */
    public ?int $filter_month = null;
    public ?int $filter_year = null;
    public ?int $filter_week = null;
    public ?int $filter_treatment_id = null;
    public ?int $filter_category_id = null;
    public ?string $filter_payment_status = 'sudah_dibayar';
    public ?string $filter_search = '';
    public ?string $filter_sort_field = 'total';
    public ?string $filter_sort_dir = 'desc';

    public int $per_page = 10;
    public int $page = 1;

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'reports/treatment-sales';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Laporan Treatment';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Laporan Treatment';
    }

    public function getSubheading(): ?string
    {
        return 'Ringkasan, rekap mingguan, dan detail penjualan treatment berdasarkan filter yang aktif.';
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
        $this->filter_treatment_id = null;
        $this->filter_category_id = null;
        $this->filter_payment_status = 'sudah_dibayar';
        $this->filter_search = '';
        $this->filter_sort_field = 'total';
        $this->filter_sort_dir = 'desc';
        $this->page = 1;
    }

    protected function service(): TreatmentSalesReportService
    {
        return new TreatmentSalesReportService([
            'month' => $this->filter_month,
            'year' => $this->filter_year,
            'week' => $this->filter_week,
            'treatment_id' => $this->filter_treatment_id,
            'category_id' => $this->filter_category_id,
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
            'chartRevenue' => $service->revenueByWeekChart(),
            'chartTopTreatments' => $service->topTreatmentsChart(),
            'chartQty' => $service->qtyByWeekChart(),
            'periodLabel' => $service->periodLabel(),
            'monthOptions' => TreatmentSalesReportService::monthOptions(),
            'yearOptions' => TreatmentSalesReportService::yearOptions(),
            'weekOptions' => TreatmentSalesReportService::weekOptions(),
            'treatmentsOptions' => TreatmentSalesReportService::treatmentsOptions(),
            'categoriesOptions' => TreatmentSalesReportService::categoriesOptions(),
            'paymentStatusOptions' => TreatmentSalesReportService::paymentStatusOptions(),
        ];
    }

    public function exportExcel()
    {
        return Excel::download(new TreatmentSalesReportExport($this->service()), $this->buildFilename('xlsx'));
    }

    public function exportCsv()
    {
        return Excel::download(
            new \App\Exports\TreatmentSalesDetailSheet($this->service()),
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.treatment-sales-report', $data)
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->stream()),
            $this->buildFilename('pdf')
        );
    }

    protected function buildFilename(string $ext): string
    {
        $month = TreatmentSalesReportService::monthOptions()[$this->filter_month] ?? $this->filter_month;
        $base = sprintf('laporan-treatment-%s-%s', strtolower($month), $this->filter_year);
        if ($this->filter_week) {
            $base .= '-minggu-' . $this->filter_week;
        }
        return "{$base}-" . now()->format('YmdHis') . ".{$ext}";
    }
}
