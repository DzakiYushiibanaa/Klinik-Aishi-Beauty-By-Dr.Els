<?php

namespace App\Filament\Pages;

use App\Exports\ProductSalesReportExport;
use App\Services\ProductSalesReportService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class ProductSalesReport extends Page
{
    protected string $view = 'filament.pages.product-sales-report';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Laporan Penjualan Produk';

    protected static ?int $navigationSort = 90;

    /**
     * Akses untuk admin dan super_admin.
     * Shield akan generate permission `view_product::sales::report` saat
     * `php artisan shield:generate` dijalankan; sampai itu kita gate manual
     * agar halaman langsung bisa diakses oleh role admin.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Super admin selalu boleh (Shield Gate::before).
        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return true;
        }

        // Admin & apoteker boleh akses laporan.
        return method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['admin', 'apoteker'])
            : false;
    }

    /** Filter state */
    public ?int $filter_month = null;
    public ?int $filter_year = null;
    public ?int $filter_week = null;
    public ?int $filter_product_id = null;
    public ?int $filter_category_id = null;
    public ?string $filter_payment_status = 'sudah_dibayar';
    public ?string $filter_search = '';
    public ?string $filter_sort_field = 'total';
    public ?string $filter_sort_dir = 'desc';

    /** Pagination state (per-halaman tabel) */
    public int $per_page = 10;
    public int $page = 1;

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'reports/product-sales';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Laporan Penjualan Produk';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Laporan Penjualan Produk';
    }

    public function getSubheading(): ?string
    {
        return 'Ringkasan, rekap mingguan, dan detail penjualan produk berdasarkan filter yang aktif.';
    }

    public function mount(): void
    {
        $this->filter_month = (int) now()->month;
        $this->filter_year = (int) now()->year;
    }

    /** Reset pagination saat filter berubah. */
    public function updated($name): void
    {
        if (str_starts_with($name, 'filter_')) {
            $this->page = 1;
        }
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function previousPage(): void
    {
        if ($this->page > 1) $this->page--;
    }

    public function gotoPage(int $page): void
    {
        $this->page = max(1, $page);
    }

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
        $this->filter_product_id = null;
        $this->filter_category_id = null;
        $this->filter_payment_status = 'sudah_dibayar';
        $this->filter_search = '';
        $this->filter_sort_field = 'total';
        $this->filter_sort_dir = 'desc';
        $this->page = 1;
    }

    /** Build service from current filter state. */
    protected function service(): ProductSalesReportService
    {
        return new ProductSalesReportService([
            'month' => $this->filter_month,
            'year' => $this->filter_year,
            'week' => $this->filter_week,
            'product_id' => $this->filter_product_id,
            'category_id' => $this->filter_category_id,
            'payment_status' => $this->filter_payment_status,
            'search' => $this->filter_search,
            'sort_field' => $this->filter_sort_field,
            'sort_dir' => $this->filter_sort_dir,
        ]);
    }

    /** Property untuk view, dipanggil tiap render Livewire. */
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
            'chartTopProducts' => $service->topProductsChart(),
            'chartQty' => $service->qtyByWeekChart(),
            'periodLabel' => $service->periodLabel(),
            'monthOptions' => ProductSalesReportService::monthOptions(),
            'yearOptions' => ProductSalesReportService::yearOptions(),
            'weekOptions' => ProductSalesReportService::weekOptions(),
            'productsOptions' => ProductSalesReportService::productsOptions(),
            'categoriesOptions' => ProductSalesReportService::categoriesOptions(),
            'paymentStatusOptions' => ProductSalesReportService::paymentStatusOptions(),
        ];
    }

    /** Export Excel (.xlsx) — mengikuti filter aktif. */
    public function exportExcel()
    {
        $service = $this->service();
        $filename = $this->buildFilename('xlsx');
        return Excel::download(new ProductSalesReportExport($service), $filename);
    }

    /** Export CSV — sheet detail saja, tanpa styling. */
    public function exportCsv()
    {
        $service = $this->service();
        $filename = $this->buildFilename('csv');
        return Excel::download(
            new \App\Exports\ProductSalesDetailSheet($service),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    /** Export PDF (DomPDF). */
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.product-sales-report', $data)
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->stream()),
            $this->buildFilename('pdf')
        );
    }

    protected function buildFilename(string $ext): string
    {
        $month = ProductSalesReportService::monthOptions()[$this->filter_month] ?? $this->filter_month;
        $base = sprintf('laporan-penjualan-produk-%s-%s', strtolower($month), $this->filter_year);
        if ($this->filter_week) {
            $base .= '-minggu-' . $this->filter_week;
        }
        return "{$base}-" . now()->format('YmdHis') . ".{$ext}";
    }
}
