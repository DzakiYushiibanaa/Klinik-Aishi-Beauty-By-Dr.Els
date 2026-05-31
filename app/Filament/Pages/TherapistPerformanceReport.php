<?php

namespace App\Filament\Pages;

use App\Exports\TherapistPerformanceExport;
use App\Services\TherapistPerformanceReportService;
use BackedEnum;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class TherapistPerformanceReport extends Page
{
    protected string $view = 'filament.pages.therapist-performance-report';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Laporan Kinerja Terapis';

    protected static ?int $navigationSort = 92;

    /** Filter state */
    public ?int $filter_month = null;
    public ?int $filter_year = null;
    public ?int $filter_week = null;
    public ?int $filter_terapis_user_id = null;
    public ?string $filter_status = 'aktif';
    public ?string $filter_search = '';
    public ?string $filter_sort_field = 'total_commission';
    public ?string $filter_sort_dir = 'desc';

    public int $per_page = 10;
    public int $page = 1;

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'reports/therapist-performance';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Laporan Kinerja Terapis';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Laporan Kinerja Terapis';
    }

    public function getSubheading(): ?string
    {
        return 'Performa terapis berdasarkan treatment selesai dan pembayaran sudah dibayar.';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) return true;
        return method_exists($user, 'hasAnyRole') ? $user->hasAnyRole(['admin', 'apoteker']) : false;
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
        $this->filter_terapis_user_id = null;
        $this->filter_status = 'aktif';
        $this->filter_search = '';
        $this->filter_sort_field = 'total_commission';
        $this->filter_sort_dir = 'desc';
        $this->page = 1;
    }

    protected function service(): TherapistPerformanceReportService
    {
        return new TherapistPerformanceReportService([
            'month' => $this->filter_month,
            'year' => $this->filter_year,
            'week' => $this->filter_week,
            'terapis_user_id' => $this->filter_terapis_user_id,
            'status' => $this->filter_status,
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
            'chartTopTherapists' => $service->topTherapistsChart(),
            'chartRevenue' => $service->revenueByTherapistChart(),
            'chartCommission' => $service->commissionByTherapistChart(),
            'periodLabel' => $service->periodLabel(),
            'monthOptions' => TherapistPerformanceReportService::monthOptions(),
            'yearOptions' => TherapistPerformanceReportService::yearOptions(),
            'weekOptions' => TherapistPerformanceReportService::weekOptions(),
            'therapistOptions' => TherapistPerformanceReportService::therapistOptions(),
            'statusOptions' => TherapistPerformanceReportService::statusOptions(),
        ];
    }

    public function exportExcel()
    {
        return Excel::download(new TherapistPerformanceExport($this->service()), $this->buildFilename('xlsx'));
    }

    public function exportCsv()
    {
        return Excel::download(
            new \App\Exports\TherapistPerformanceDetailSheet($this->service()),
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.therapist-performance-report', $data)
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->stream()),
            $this->buildFilename('pdf')
        );
    }

    protected function buildFilename(string $ext): string
    {
        $month = TherapistPerformanceReportService::monthOptions()[$this->filter_month] ?? $this->filter_month;
        $base = sprintf('laporan-kinerja-terapis-%s-%s', strtolower($month), $this->filter_year);
        if ($this->filter_week) {
            $base .= '-minggu-' . $this->filter_week;
        }
        return "{$base}-" . now()->format('YmdHis') . ".{$ext}";
    }
}
