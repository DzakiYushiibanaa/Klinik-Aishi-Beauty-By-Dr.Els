<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service untuk laporan penjualan produk.
 *
 * Sumber data: order_items dengan item_type = 'product' yang ber-relasi ke
 * orders dengan filter status pembayaran. Periode default = 1 bulan, dibagi
 * 4 minggu (Minggu 1: tgl 1-7, Minggu 2: 8-14, Minggu 3: 15-21, Minggu 4: 22-akhir bulan).
 */
class ProductSalesReportService
{
    /**
     * @param  array{
     *     month?: int|null,
     *     year?: int|null,
     *     week?: int|null,
     *     product_id?: int|null,
     *     category_id?: int|null,
     *     payment_status?: string|null,
     *     search?: string|null,
     *     sort_field?: string|null,
     *     sort_dir?: string|null,
     * } $filters
     */
    public function __construct(public array $filters = [])
    {
    }

    /** Filter helpers */
    public function month(): int
    {
        return (int) ($this->filters['month'] ?? now()->month);
    }

    public function year(): int
    {
        return (int) ($this->filters['year'] ?? now()->year);
    }

    public function week(): ?int
    {
        $w = $this->filters['week'] ?? null;
        return $w ? (int) $w : null;
    }

    public function paymentStatus(): string
    {
        return $this->filters['payment_status'] ?? 'sudah_dibayar';
    }

    /** Periode awal & akhir untuk filter aktif. */
    public function periodStart(): Carbon
    {
        $base = Carbon::create($this->year(), $this->month(), 1)->startOfDay();
        if ($w = $this->week()) {
            return $base->copy()->addDays(($w - 1) * 7);
        }
        return $base;
    }

    public function periodEnd(): Carbon
    {
        $monthStart = Carbon::create($this->year(), $this->month(), 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        if ($w = $this->week()) {
            $end = $monthStart->copy()->addDays($w * 7 - 1)->endOfDay();
            return $end->greaterThan($monthEnd) ? $monthEnd : $end;
        }
        return $monthEnd;
    }

    public function periodLabel(): string
    {
        $start = $this->periodStart();
        $end = $this->periodEnd();
        return $start->translatedFormat('d M Y') . ' – ' . $end->translatedFormat('d M Y');
    }

    /** Hitung minggu ke-berapa dari sebuah tanggal di dalam bulan periode. */
    public static function weekOfMonth(Carbon $date): int
    {
        $day = $date->day;
        if ($day <= 7) return 1;
        if ($day <= 14) return 2;
        if ($day <= 21) return 3;
        return 4;
    }

    /** Base query order_items produk dengan filter aktif. */
    public function baseQuery()
    {
        $start = $this->periodStart();
        $end = $this->periodEnd();
        $paymentStatus = $this->paymentStatus();
        $productId = $this->filters['product_id'] ?? null;
        $categoryId = $this->filters['category_id'] ?? null;

        return OrderItem::query()
            ->where('item_type', 'product')
            ->whereNotNull('product_id')
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($categoryId, fn ($q) => $q->whereHas('product', fn ($pq) => $pq->where('product_category_id', $categoryId)))
            ->whereHas('order', function ($q) use ($paymentStatus, $start, $end) {
                if ($paymentStatus === 'all') {
                    $q->whereBetween('created_at', [$start, $end]);
                } elseif ($paymentStatus === 'sudah_dibayar') {
                    $q->where('status', 'sudah_dibayar')
                      ->whereBetween('paid_at', [$start, $end]);
                } else {
                    $q->where('status', $paymentStatus)
                      ->whereBetween('created_at', [$start, $end]);
                }
            });
    }

    /**
     * Ringkasan summary card.
     *
     * @return array{
     *     total_revenue:int,
     *     total_qty:int,
     *     total_transactions:int,
     *     best_seller:?array{name:string,qty:int},
     *     average_per_week:int,
     * }
     */
    public function summary(): array
    {
        $items = $this->baseQuery()->get(['product_id', 'qty', 'subtotal', 'order_id']);

        $totalRevenue = (int) $items->sum('subtotal');
        $totalQty = (int) $items->sum('qty');
        $totalTransactions = $items->pluck('order_id')->unique()->count();

        $bestSeller = null;
        if ($items->isNotEmpty()) {
            $grouped = $items->groupBy('product_id')
                ->map(fn ($g) => [
                    'product_id' => $g->first()->product_id,
                    'qty' => (int) $g->sum('qty'),
                ])
                ->sortByDesc('qty')
                ->values();

            $top = $grouped->first();
            if ($top) {
                $product = \App\Models\Product::find($top['product_id']);
                $bestSeller = [
                    'name' => $product?->nama ?? 'Tidak diketahui',
                    'qty' => $top['qty'],
                ];
            }
        }

        // Average per week dihitung dari rekap mingguan (4 minggu).
        $weeks = $this->weeklyRecap();
        $avgPerWeek = $weeks->count() > 0
            ? (int) round($weeks->avg('total_revenue'))
            : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_qty' => $totalQty,
            'total_transactions' => $totalTransactions,
            'best_seller' => $bestSeller,
            'average_per_week' => $avgPerWeek,
        ];
    }

    /**
     * Baris detail tabel laporan: satu row per produk.
     *
     * @return Collection<int, array{
     *     product_id:int,
     *     name:string,
     *     category:string,
     *     price:int,
     *     qty:int,
     *     total:int,
     *     week_label:string,
     *     period_label:string,
     * }>
     */
    public function tableRows(): Collection
    {
        $items = $this->baseQuery()
            ->with(['product.category', 'order'])
            ->get();

        // Group per produk
        $grouped = $items->groupBy('product_id')->map(function ($rows) {
            $first = $rows->first();
            $product = $first->product;
            $qty = (int) $rows->sum('qty');
            $total = (int) $rows->sum('subtotal');
            $price = $product?->harga ?? ($qty > 0 ? (int) round($total / $qty) : 0);

            // Tentukan minggu utama (mode dari minggu yang muncul)
            $weeks = $rows->map(function ($r) {
                $date = $r->order->paid_at ?? $r->order->created_at ?? now();
                return self::weekOfMonth($date instanceof Carbon ? $date : Carbon::parse($date));
            })->unique()->sort()->values();

            $weekLabel = $weeks->isEmpty()
                ? '—'
                : 'Minggu ' . $weeks->implode(', ');

            return [
                'product_id' => (int) $first->product_id,
                'name' => $product?->nama ?? 'Produk dihapus',
                'category' => $product?->category?->nama ?? '—',
                'price' => (int) $price,
                'qty' => $qty,
                'total' => $total,
                'week_label' => $weekLabel,
                'period_label' => $this->periodLabel(),
            ];
        })->values();

        // Search & sort
        if (!empty($this->filters['search'])) {
            $q = mb_strtolower($this->filters['search']);
            $grouped = $grouped->filter(fn ($r) =>
                str_contains(mb_strtolower($r['name']), $q) ||
                str_contains(mb_strtolower($r['category']), $q)
            )->values();
        }

        $sortField = $this->filters['sort_field'] ?? 'total';
        $sortDir = $this->filters['sort_dir'] ?? 'desc';
        $allowedSort = ['name', 'category', 'price', 'qty', 'total'];
        if (!in_array($sortField, $allowedSort, true)) {
            $sortField = 'total';
        }
        $grouped = $sortDir === 'asc'
            ? $grouped->sortBy($sortField)->values()
            : $grouped->sortByDesc($sortField)->values();

        return $grouped;
    }

    /**
     * Rekap mingguan untuk bulan periode aktif (selalu 4 minggu).
     *
     * @return Collection<int, array{
     *     week:int,
     *     label:string,
     *     range:string,
     *     total_qty:int,
     *     total_revenue:int,
     *     best_product:?string,
     * }>
     */
    public function weeklyRecap(): Collection
    {
        $monthStart = Carbon::create($this->year(), $this->month(), 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $paymentStatus = $this->paymentStatus();
        $productId = $this->filters['product_id'] ?? null;
        $categoryId = $this->filters['category_id'] ?? null;

        // Ambil semua item produk dalam BULAN penuh (terlepas dari filter week)
        $items = OrderItem::query()
            ->where('item_type', 'product')
            ->whereNotNull('product_id')
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($categoryId, fn ($q) => $q->whereHas('product', fn ($pq) => $pq->where('product_category_id', $categoryId)))
            ->whereHas('order', function ($q) use ($paymentStatus, $monthStart, $monthEnd) {
                if ($paymentStatus === 'all') {
                    $q->whereBetween('created_at', [$monthStart, $monthEnd]);
                } elseif ($paymentStatus === 'sudah_dibayar') {
                    $q->where('status', 'sudah_dibayar')
                      ->whereBetween('paid_at', [$monthStart, $monthEnd]);
                } else {
                    $q->where('status', $paymentStatus)
                      ->whereBetween('created_at', [$monthStart, $monthEnd]);
                }
            })
            ->with(['order', 'product'])
            ->get();

        $weeks = collect([1, 2, 3, 4])->map(function ($w) use ($items, $monthStart, $monthEnd) {
            $start = $monthStart->copy()->addDays(($w - 1) * 7);
            // Minggu 4 selalu sampai akhir bulan (mis. Mei = 31 hari, jadi Minggu 4 = 22-31).
            $end = $w === 4
                ? $monthEnd->copy()
                : $monthStart->copy()->addDays($w * 7 - 1)->endOfDay();
            if ($end->greaterThan($monthEnd)) $end = $monthEnd;

            $bucket = $items->filter(function ($item) use ($w) {
                $date = $item->order->paid_at ?? $item->order->created_at ?? null;
                if (!$date) return false;
                $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
                return self::weekOfMonth($carbon) === $w;
            });

            $totalQty = (int) $bucket->sum('qty');
            $totalRevenue = (int) $bucket->sum('subtotal');

            $best = null;
            if ($bucket->isNotEmpty()) {
                $g = $bucket->groupBy('product_id')
                    ->map(fn ($rows) => [
                        'name' => $rows->first()->product?->nama ?? '—',
                        'qty' => (int) $rows->sum('qty'),
                    ])
                    ->sortByDesc('qty')
                    ->first();
                $best = $g['name'] ?? null;
            }

            return [
                'week' => $w,
                'label' => "Minggu $w",
                'range' => $start->translatedFormat('d M') . ' – ' . $end->translatedFormat('d M'),
                'total_qty' => $totalQty,
                'total_revenue' => $totalRevenue,
                'best_product' => $best,
            ];
        });

        return $weeks;
    }

    /**
     * Data untuk chart pendapatan per minggu.
     */
    public function revenueByWeekChart(): array
    {
        $weeks = $this->weeklyRecap();
        return [
            'labels' => $weeks->pluck('label')->toArray(),
            'data' => $weeks->pluck('total_revenue')->toArray(),
        ];
    }

    /** Top 5 produk terlaris (qty). */
    public function topProductsChart(int $limit = 5): array
    {
        $rows = $this->tableRows()
            ->sortByDesc('qty')
            ->take($limit)
            ->values();

        return [
            'labels' => $rows->pluck('name')->toArray(),
            'data' => $rows->pluck('qty')->toArray(),
        ];
    }

    /** Total qty terjual per minggu (chart line/bar). */
    public function qtyByWeekChart(): array
    {
        $weeks = $this->weeklyRecap();
        return [
            'labels' => $weeks->pluck('label')->toArray(),
            'data' => $weeks->pluck('total_qty')->toArray(),
        ];
    }

    /** Total agregat di footer tabel. */
    public function tableTotals(): array
    {
        $rows = $this->tableRows();
        return [
            'total_qty' => (int) $rows->sum('qty'),
            'total_revenue' => (int) $rows->sum('total'),
            'product_count' => $rows->count(),
        ];
    }

    /** Daftar opsi untuk filter. */
    public static function categoriesOptions(): array
    {
        return ProductCategory::orderBy('nama')
            ->where('is_active', true)
            ->pluck('nama', 'product_category_id')
            ->toArray();
    }

    public static function productsOptions(): array
    {
        return \App\Models\Product::orderBy('nama')
            ->where('is_active', true)
            ->pluck('nama', 'product_id')
            ->toArray();
    }

    public static function paymentStatusOptions(): array
    {
        return [
            'sudah_dibayar' => 'Sudah Dibayar',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'dibatalkan' => 'Dibatalkan',
            'all' => 'Semua Status',
        ];
    }

    public static function monthOptions(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    public static function yearOptions(): array
    {
        $current = (int) now()->year;
        $years = [];
        for ($y = $current; $y >= $current - 4; $y--) {
            $years[$y] = (string) $y;
        }
        return $years;
    }

    public static function weekOptions(): array
    {
        return [
            '' => 'Semua Minggu',
            1 => 'Minggu 1 (1–7)',
            2 => 'Minggu 2 (8–14)',
            3 => 'Minggu 3 (15–21)',
            4 => 'Minggu 4 (22–akhir)',
        ];
    }
}
