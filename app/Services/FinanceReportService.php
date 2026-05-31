<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service laporan keuangan klinik.
 *
 * Sumber data:
 * - orders dengan status = 'sudah_dibayar' (paid)
 * - order_items untuk breakdown per jenis (produk/treatment/konsultasi/membership)
 *
 * Filter periode pakai orders.paid_at.
 *
 * RULE Revenue klinik:
 *   revenue = subtotal - diskon_total
 * Total pembayaran customer:
 *   grand_total = subtotal + ongkir - diskon_total
 *
 * Ongkir hanya untuk order_type='produk', tidak dianggap revenue klinik.
 */
class FinanceReportService
{
    /** Status yang dianggap sudah dibayar. */
    protected const PAID_STATUSES = ['sudah_dibayar'];

    public function __construct(public array $filters = [])
    {
    }

    public function month(): int { return (int) ($this->filters['month'] ?? now()->month); }
    public function year(): int { return (int) ($this->filters['year'] ?? now()->year); }
    public function week(): ?int
    {
        $w = $this->filters['week'] ?? null;
        return $w ? (int) $w : null;
    }
    public function orderType(): string { return $this->filters['order_type'] ?? 'all'; }
    public function paymentMethod(): string { return $this->filters['payment_method'] ?? 'all'; }
    public function paymentStatus(): string { return $this->filters['payment_status'] ?? 'sudah_dibayar'; }

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
            $end = $w === 4
                ? $monthEnd->copy()
                : $monthStart->copy()->addDays($w * 7 - 1)->endOfDay();
            return $end->greaterThan($monthEnd) ? $monthEnd : $end;
        }
        return $monthEnd;
    }

    public function periodLabel(): string
    {
        return $this->periodStart()->translatedFormat('d M Y') . ' – ' . $this->periodEnd()->translatedFormat('d M Y');
    }

    public static function weekOfMonth(Carbon $date): int
    {
        $day = $date->day;
        if ($day <= 7) return 1;
        if ($day <= 14) return 2;
        if ($day <= 21) return 3;
        return 4;
    }

    /**
     * Resolve daftar nilai status order:
     * - 'paid' / 'sudah_dibayar' → ['sudah_dibayar']
     * - 'all'                    → null (skip filter)
     * - lainnya                  → exact match
     */
    protected function resolveStatuses(): ?array
    {
        $f = $this->paymentStatus();
        if ($f === 'all') return null;
        if (in_array($f, ['paid', 'sudah_dibayar'], true)) return self::PAID_STATUSES;
        return [$f];
    }

    /** Base query orders pada periode aktif dengan filter status & order type. */
    public function baseQuery()
    {
        $start = $this->periodStart();
        $end = $this->periodEnd();
        $statuses = $this->resolveStatuses();
        $orderType = $this->orderType();
        $paymentMethod = $this->paymentMethod();
        $search = $this->filters['search'] ?? null;

        return Order::query()
            ->when($statuses !== null, fn ($q) => $q->whereIn('status', $statuses))
            ->whereBetween('paid_at', [$start, $end])
            ->when($orderType !== 'all', fn ($q) => $q->where('order_type', $orderType))
            ->when($paymentMethod !== 'all', function ($q) use ($paymentMethod) {
                if ($paymentMethod === 'unknown') {
                    $q->whereNull('payment_method');
                } else {
                    $q->where('payment_method', $paymentMethod);
                }
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('kode_order', 'like', "%{$search}%")
                      ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('items', function ($iq) use ($search) {
                          $iq->where('description', 'like', "%{$search}%")
                            ->orWhereHas('treatment', fn ($tq) => $tq->where('nama', 'like', "%{$search}%"))
                            ->orWhereHas('product', fn ($pq) => $pq->where('nama', 'like', "%{$search}%"));
                      });
                });
            });
    }

    /** Summary cards. */
    public function summary(): array
    {
        $orders = $this->baseQuery()->get(['order_id', 'order_type', 'subtotal', 'diskon_total', 'ongkir', 'grand_total']);

        $totalRevenue = (int) $orders->sum(fn ($o) => max(0, $o->subtotal - $o->diskon_total));
        $totalOngkir = (int) $orders->sum('ongkir');
        $totalTransactions = $orders->count();

        // Breakdown per type pakai order_items biar akurat (membership ada di order_items juga)
        $breakdown = $this->revenueByType();

        return [
            'total_revenue' => $totalRevenue,
            'revenue_produk' => $breakdown['produk'] ?? 0,
            'revenue_treatment' => $breakdown['treatment'] ?? 0,
            'revenue_konsultasi' => $breakdown['konsultasi'] ?? 0,
            'revenue_membership' => $breakdown['membership'] ?? 0,
            'revenue_lainnya' => $breakdown['lainnya'] ?? 0,
            'total_ongkir' => $totalOngkir,
            'total_transactions' => $totalTransactions,
        ];
    }

    /**
     * Breakdown revenue per item_type via order_items.subtotal,
     * tetap mengikuti filter order yang aktif.
     */
    public function revenueByType(): array
    {
        $orderIds = $this->baseQuery()->pluck('order_id');
        if ($orderIds->isEmpty()) {
            return ['produk' => 0, 'treatment' => 0, 'konsultasi' => 0, 'membership' => 0, 'lainnya' => 0];
        }

        $rows = OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->selectRaw('item_type, SUM(subtotal) as total')
            ->groupBy('item_type')
            ->get();

        $byType = [
            'produk' => 0,
            'treatment' => 0,
            'konsultasi' => 0,
            'membership' => 0,
            'lainnya' => 0,
        ];

        foreach ($rows as $row) {
            $key = match ($row->item_type) {
                'product', 'produk' => 'produk',
                'treatment' => 'treatment',
                'konsultasi', 'consultation' => 'konsultasi',
                'membership' => 'membership',
                default => 'lainnya',
            };
            $byType[$key] += (int) $row->total;
        }

        return $byType;
    }

    /**
     * Tabel detail: 1 baris per order.
     */
    public function tableRows(): Collection
    {
        $orders = $this->baseQuery()
            ->with(['user'])
            ->get();

        $rows = $orders->map(function ($order) {
            $paidAt = $order->paid_at ?? $order->created_at ?? now();
            $paidAt = $paidAt instanceof Carbon ? $paidAt : Carbon::parse($paidAt);
            $week = self::weekOfMonth($paidAt);

            return [
                'order_id' => (int) $order->order_id,
                'kode_order' => $order->kode_order ?? '—',
                'paid_at' => $paidAt,
                'paid_at_label' => $paidAt->translatedFormat('d M Y, H:i'),
                'pasien' => $order->user?->name ?? '—',
                'order_type' => $order->order_type ?? '—',
                'order_type_label' => self::orderTypeLabel($order->order_type),
                'payment_method' => $order->payment_method ?? '—',
                'payment_method_label' => self::paymentMethodLabel($order->payment_method),
                'status' => $order->status,
                'status_label' => self::statusLabel($order->status),
                'subtotal' => (int) $order->subtotal,
                'ongkir' => (int) $order->ongkir,
                'diskon' => (int) $order->diskon_total,
                'grand_total' => (int) $order->grand_total,
                'revenue' => (int) max(0, $order->subtotal - $order->diskon_total),
                'week' => $week,
            ];
        });

        // Sorting
        $sortField = $this->filters['sort_field'] ?? 'paid_at';
        $sortDir = $this->filters['sort_dir'] ?? 'desc';
        $allowedSort = ['kode_order', 'paid_at', 'pasien', 'order_type', 'payment_method', 'subtotal', 'ongkir', 'diskon', 'grand_total'];
        if (!in_array($sortField, $allowedSort, true)) {
            $sortField = 'paid_at';
        }

        return $sortDir === 'asc'
            ? $rows->sortBy($sortField)->values()
            : $rows->sortByDesc($sortField)->values();
    }

    /** Rekap mingguan. */
    public function weeklyRecap(): Collection
    {
        $monthStart = Carbon::create($this->year(), $this->month(), 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Bypass week filter
        $monthService = new self(array_merge($this->filters, ['week' => null]));
        $orders = $monthService->baseQuery()->get(['order_id', 'order_type', 'subtotal', 'diskon_total', 'ongkir', 'grand_total', 'paid_at']);

        // Map order_id → item type breakdown
        $orderIds = $orders->pluck('order_id');
        $itemRevenue = OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->select('order_id', 'item_type', 'subtotal')
            ->get()
            ->groupBy('order_id');

        return collect([1, 2, 3, 4])->map(function ($w) use ($orders, $monthStart, $monthEnd, $itemRevenue) {
            $start = $monthStart->copy()->addDays(($w - 1) * 7);
            $end = $w === 4
                ? $monthEnd->copy()
                : $monthStart->copy()->addDays($w * 7 - 1)->endOfDay();
            if ($end->greaterThan($monthEnd)) $end = $monthEnd;

            $bucket = $orders->filter(function ($o) use ($w) {
                $paidAt = $o->paid_at;
                if (!$paidAt) return false;
                $carbon = $paidAt instanceof Carbon ? $paidAt : Carbon::parse($paidAt);
                return self::weekOfMonth($carbon) === $w;
            });

            $totalRevenue = (int) $bucket->sum(fn ($o) => max(0, $o->subtotal - $o->diskon_total));
            $totalOngkir = (int) $bucket->sum('ongkir');
            $totalTransactions = $bucket->count();

            $produk = 0;
            $treatment = 0;
            $konsultasi = 0;
            foreach ($bucket as $o) {
                $items = $itemRevenue->get($o->order_id, collect());
                foreach ($items as $it) {
                    $sub = (int) $it->subtotal;
                    switch ($it->item_type) {
                        case 'product':
                        case 'produk':
                            $produk += $sub; break;
                        case 'treatment':
                            $treatment += $sub; break;
                        case 'konsultasi':
                        case 'consultation':
                            $konsultasi += $sub; break;
                    }
                }
            }

            return [
                'week' => $w,
                'label' => "Minggu $w",
                'range' => $start->translatedFormat('d M') . ' – ' . $end->translatedFormat('d M'),
                'total_revenue' => $totalRevenue,
                'revenue_produk' => $produk,
                'revenue_treatment' => $treatment,
                'revenue_konsultasi' => $konsultasi,
                'total_ongkir' => $totalOngkir,
                'total_transactions' => $totalTransactions,
            ];
        });
    }

    public function revenueByWeekChart(): array
    {
        $weeks = $this->weeklyRecap();
        return [
            'labels' => $weeks->pluck('label')->toArray(),
            'data' => $weeks->pluck('total_revenue')->toArray(),
        ];
    }

    /** Doughnut: komposisi pendapatan (Produk/Treatment/Konsultasi). */
    public function revenueCompositionChart(): array
    {
        $b = $this->revenueByType();
        $labels = ['Produk', 'Treatment', 'Konsultasi'];
        $data = [
            $b['produk'] ?? 0,
            $b['treatment'] ?? 0,
            $b['konsultasi'] ?? 0,
        ];
        // Tambahkan Membership/Lainnya kalau ada nilainya
        if (!empty($b['membership'])) {
            $labels[] = 'Membership';
            $data[] = $b['membership'];
        }
        if (!empty($b['lainnya'])) {
            $labels[] = 'Lainnya';
            $data[] = $b['lainnya'];
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /** Bar: distribusi metode pembayaran. */
    public function paymentMethodChart(): array
    {
        $rows = $this->baseQuery()
            ->selectRaw('COALESCE(payment_method, "unknown") as pm, SUM(grand_total) as total')
            ->groupBy('pm')
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $data = [];
        foreach ($rows as $r) {
            $labels[] = self::paymentMethodLabel($r->pm);
            $data[] = (int) $r->total;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public function tableTotals(): array
    {
        $rows = $this->tableRows();
        return [
            'subtotal' => (int) $rows->sum('subtotal'),
            'ongkir' => (int) $rows->sum('ongkir'),
            'diskon' => (int) $rows->sum('diskon'),
            'grand_total' => (int) $rows->sum('grand_total'),
            'revenue' => (int) $rows->sum('revenue'),
            'transaction_count' => $rows->count(),
        ];
    }

    /** Daftar metode pembayaran dinamis dari DB (untuk filter dropdown). */
    public static function paymentMethodOptions(): array
    {
        $methods = Order::query()
            ->whereNotNull('payment_method')
            ->where('payment_method', '<>', '')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method')
            ->toArray();

        $opts = ['all' => 'Semua Metode'];
        foreach ($methods as $m) {
            $opts[$m] = self::paymentMethodLabel($m);
        }
        // Tambah "Belum Tercatat" kalau ada order paid tanpa method
        $hasNull = Order::whereIn('status', self::PAID_STATUSES)->whereNull('payment_method')->exists();
        if ($hasNull) {
            $opts['unknown'] = 'Belum Tercatat';
        }
        return $opts;
    }

    public static function orderTypeOptions(): array
    {
        return [
            'all' => 'Semua Jenis',
            'produk' => 'Produk',
            'treatment' => 'Treatment',
            'konsultasi' => 'Konsultasi',
            'membership' => 'Membership',
        ];
    }

    public static function paymentStatusOptions(): array
    {
        return [
            'sudah_dibayar' => 'Sudah Dibayar',
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

    /** Label ramah untuk payment_method (Midtrans/manual/cash, dll). */
    public static function paymentMethodLabel(?string $method): string
    {
        if (!$method) return 'Belum Tercatat';
        $map = [
            'bank_transfer' => 'Bank Transfer',
            'manual_transfer' => 'Transfer Manual',
            'qris' => 'QRIS',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'ovo' => 'OVO',
            'dana' => 'DANA',
            'credit_card' => 'Kartu Kredit',
            'echannel' => 'Mandiri Bill',
            'cstore' => 'Convenience Store',
            'cash' => 'Tunai',
            'midtrans' => 'Midtrans',
            'unknown' => 'Belum Tercatat',
        ];
        if (isset($map[$method])) return $map[$method];
        // Default: capitalize each word, replace _
        return ucwords(str_replace('_', ' ', $method));
    }

    public static function orderTypeLabel(?string $type): string
    {
        return match ($type) {
            'produk' => 'Produk',
            'treatment' => 'Treatment',
            'konsultasi' => 'Konsultasi',
            'membership' => 'Membership',
            default => $type ? ucfirst($type) : '—',
        };
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'sudah_dibayar' => 'Sudah Dibayar',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'dibatalkan', 'batal' => 'Dibatalkan',
            'expired' => 'Kadaluarsa',
            'draft' => 'Draft',
            default => $status ? ucfirst(str_replace('_', ' ', $status)) : '—',
        };
    }
}
