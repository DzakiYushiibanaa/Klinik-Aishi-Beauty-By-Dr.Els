<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\TreatmentCategory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service untuk laporan penjualan treatment.
 *
 * RULE FINAL (v2 — fix logic):
 * Treatment masuk laporan HANYA jika:
 *   1. order_items.item_type = 'treatment'
 *   2. orders.status = 'sudah_dibayar'         (bisa diubah via filter, default 'sudah_dibayar')
 *   3. appointments.status = 'selesai'         (WAJIB, tidak bisa di-bypass user)
 *
 * Booking treatment yang sudah dibayar tapi appointment belum selesai
 * (status: diajukan/disetujui/menunggu_pembayaran/batal/tidak_hadir) TIDAK
 * masuk laporan ini — ada di laporan keuangan terpisah.
 *
 * Periode filtering pakai tanggal selesai appointment:
 *   appointment.updated_at  (tabel appointments tidak punya completed_at,
 *                            tapi updated_at tercatat saat status diubah ke 'selesai')
 * Fallback: order.paid_at → order.created_at.
 *
 * Setiap baris tabel = 1 transaksi order_item (bukan grouped per treatment).
 */
class TreatmentSalesReportService
{
    /** Status order yang dianggap sudah dibayar. */
    protected const PAID_STATUSES = ['sudah_dibayar'];

    /** Status appointment yang wajib supaya masuk laporan treatment. */
    protected const COMPLETED_APPOINTMENT_STATUSES = ['selesai', 'completed'];

    public function __construct(public array $filters = [])
    {
    }

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
     * Resolve daftar nilai status order yang harus dipakai berdasarkan filter.
     * - 'sudah_dibayar' → ['sudah_dibayar']
     * - 'all'           → null (skip filter status order, hanya cek appointment)
     * - lainnya         → exact match
     */
    protected function resolveOrderStatuses(): ?array
    {
        $filter = $this->paymentStatus();
        if ($filter === 'all') return null;
        if ($filter === 'sudah_dibayar') return self::PAID_STATUSES;
        return [$filter];
    }

    /**
     * Tanggal selesai treatment (untuk filter periode + bucket minggu).
     * Prioritas:
     *   1. appointment.updated_at  (mencerminkan tgl status berubah ke 'selesai')
     *   2. order.paid_at
     *   3. order.created_at
     */
    public static function completionDate(?\App\Models\OrderItem $item): ?Carbon
    {
        if (!$item) return null;
        $apt = $item->appointment ?? null;
        $order = $item->order ?? null;

        $candidate = $apt?->updated_at
            ?? $order?->paid_at
            ?? $order?->created_at;

        if (!$candidate) return null;
        return $candidate instanceof Carbon ? $candidate : Carbon::parse($candidate);
    }

    /**
     * Base query untuk treatment items pada periode aktif.
     *
     * WAJIB:
     * - item_type = 'treatment'
     * - appointment_id NOT NULL (treatment harus terkait dengan janji temu)
     * - appointment.status = 'selesai'
     *
     * Status order: sesuai filter (default 'sudah_dibayar').
     *
     * Filter periode menggunakan appointment.updated_at (proxy untuk tanggal
     * selesai treatment), bukan tanggal pembayaran.
     *
     * Catatan: order_items.treatment_id BISA NULL (data legacy admin POS),
     * untuk kasus itu treatment data resolve via appointment_items pada
     * tableRows()/weeklyRecap().
     */
    public function baseQuery()
    {
        $start = $this->periodStart();
        $end = $this->periodEnd();
        $orderStatuses = $this->resolveOrderStatuses();
        $treatmentId = $this->filters['treatment_id'] ?? null;
        $categoryId = $this->filters['category_id'] ?? null;
        $search = $this->filters['search'] ?? null;

        return OrderItem::query()
            ->where('order_items.item_type', 'treatment')
            ->whereNotNull('order_items.appointment_id')
            ->when($treatmentId, fn ($q) => $q->where(function ($w) use ($treatmentId) {
                // Filter treatment_id: cocok di order_items ATAU di appointment_items
                $w->where('order_items.treatment_id', $treatmentId)
                  ->orWhereHas('appointment.items', fn ($iq) =>
                      $iq->where('item_type', 'treatment')->where('treatment_id', $treatmentId)
                  );
            }))
            ->when($categoryId, fn ($q) => $q->where(function ($w) use ($categoryId) {
                $w->whereHas('treatment', fn ($tq) => $tq->where('treatment_category_id', $categoryId))
                  ->orWhereHas('appointment.items.treatment', fn ($tq) => $tq->where('treatment_category_id', $categoryId));
            }))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->whereHas('treatment', fn ($tq) => $tq->where('nama', 'like', "%{$search}%"))
                      ->orWhereHas('appointment.items.treatment', fn ($tq) => $tq->where('nama', 'like', "%{$search}%"))
                      ->orWhereHas('order', fn ($oq) => $oq->where('kode_order', 'like', "%{$search}%"))
                      ->orWhereHas('order.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            })
            // Filter status order (kalau user pilih spesifik). 'all' → skip filter status order.
            ->when($orderStatuses !== null, fn ($q) => $q->whereHas('order', fn ($oq) => $oq->whereIn('status', $orderStatuses)))
            // WAJIB: appointment harus selesai + dalam periode (pakai updated_at).
            ->whereHas('appointment', function ($q) use ($start, $end) {
                $q->whereIn('status', self::COMPLETED_APPOINTMENT_STATUSES)
                  ->whereBetween('updated_at', [$start, $end]);
            });
    }

    /** Summary cards. */
    public function summary(): array
    {
        // Pakai expanded rows (satu row per appointment_items.treatment) supaya
        // qty/revenue konsisten dengan tabel detail & weekly recap.
        $rows = $this->expandedRawRows();

        $totalRevenue = (int) $rows->sum('total');
        $totalQty = (int) $rows->sum('qty');
        $totalTransactions = $rows->pluck('order_id')->unique()->count();

        $bestSeller = null;
        if ($rows->isNotEmpty()) {
            $top = $rows->groupBy('treatment_id')
                ->map(fn ($g) => [
                    'treatment_id' => $g->first()['treatment_id'],
                    'name' => $g->first()['treatment_name'],
                    'qty' => (int) $g->sum('qty'),
                ])
                ->sortByDesc('qty')
                ->first();
            if ($top) {
                $bestSeller = [
                    'name' => $top['name'],
                    'qty' => $top['qty'],
                ];
            }
        }

        $weeks = $this->weeklyRecap();
        $avgPerWeek = $weeks->count() > 0 ? (int) round($weeks->avg('total_revenue')) : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_qty' => $totalQty,
            'total_transactions' => $totalTransactions,
            'best_seller' => $bestSeller,
            'average_per_week' => $avgPerWeek,
        ];
    }

    /**
     * Expand baseQuery() rows ke level treatment.
     *
     * Logika:
     * - Jika order_item.treatment_id ADA → 1 row (langsung pakai data order_item)
     * - Jika order_item.treatment_id NULL → expand via appointment_items.item_type='treatment'
     *   harga_satuan = treatment.harga, qty = appointment_item.qty, total = harga × qty
     *
     * Hasil: 1 row per (order_item × appointment_items_treatment).
     */
    public function expandedRawRows(): Collection
    {
        $items = $this->baseQuery()
            ->with(['treatment.category', 'order.user', 'appointment.items.treatment.category'])
            ->get();

        $rows = collect();

        foreach ($items as $item) {
            $order = $item->order;
            $apt = $item->appointment;
            $date = self::completionDate($item) ?? now();
            $week = self::weekOfMonth($date);
            $kode = $order?->kode_order ?? '—';
            $pasien = $order?->user?->name ?? '—';

            $orderTreatmentId = $item->treatment_id;

            if ($orderTreatmentId && $item->treatment) {
                // Mode A: order_item sudah punya treatment_id (data baru)
                $rows->push([
                    'order_item_id' => (int) $item->order_item_id,
                    'kode_order' => $kode,
                    'pasien' => $pasien,
                    'treatment_id' => (int) $orderTreatmentId,
                    'treatment_name' => $item->treatment->nama,
                    'category' => $item->treatment->category?->nama ?? '—',
                    'price' => (int) $item->harga_satuan,
                    'qty' => (int) $item->qty,
                    'total' => (int) $item->subtotal,
                    'order_id' => (int) $item->order_id,
                    'week' => $week,
                    'week_label' => "Minggu {$week}",
                    'period_label' => $this->periodLabel(),
                    'date' => $date,
                    'appointment_status' => $apt?->status ?? 'selesai',
                    'appointment_status_label' => 'Selesai',
                ]);
                continue;
            }

            // Mode B: order_item.treatment_id NULL → expand dari appointment_items
            $aptItems = $apt?->items?->where('item_type', 'treatment')->where('treatment_id', '!=', null) ?? collect();

            if ($aptItems->isEmpty()) {
                // Tidak ada appointment_items detail; skip — tidak bisa identify treatment.
                continue;
            }

            foreach ($aptItems as $aptItem) {
                $treatment = $aptItem->treatment;
                if (!$treatment) continue;
                $price = (int) $treatment->harga;
                $qty = (int) $aptItem->qty;
                $rows->push([
                    'order_item_id' => (int) $item->order_item_id,
                    'kode_order' => $kode,
                    'pasien' => $pasien,
                    'treatment_id' => (int) $treatment->treatment_id,
                    'treatment_name' => $treatment->nama,
                    'category' => $treatment->category?->nama ?? '—',
                    'price' => $price,
                    'qty' => $qty,
                    'total' => $price * $qty,
                    'order_id' => (int) $item->order_id,
                    'week' => $week,
                    'week_label' => "Minggu {$week}",
                    'period_label' => $this->periodLabel(),
                    'date' => $date,
                    'appointment_status' => $apt?->status ?? 'selesai',
                    'appointment_status_label' => 'Selesai',
                ]);
            }
        }

        // Filter ulang treatment_id / category_id (untuk Mode B yang mungkin tidak ke-filter di whereHas)
        $treatmentIdFilter = $this->filters['treatment_id'] ?? null;
        $categoryIdFilter = $this->filters['category_id'] ?? null;
        if ($treatmentIdFilter) {
            $rows = $rows->where('treatment_id', (int) $treatmentIdFilter);
        }
        if ($categoryIdFilter) {
            $catName = TreatmentCategory::find($categoryIdFilter)?->nama;
            if ($catName) {
                $rows = $rows->where('category', $catName);
            }
        }

        return $rows->values();
    }

    /**
     * Tabel detail: 1 baris per (order_item × treatment), dengan info
     * kode_order + nama pasien + status appointment (selalu 'Selesai').
     */
    public function tableRows(): Collection
    {
        $rows = $this->expandedRawRows();

        // Sorting
        $sortField = $this->filters['sort_field'] ?? 'total';
        $sortDir = $this->filters['sort_dir'] ?? 'desc';
        $allowedSort = ['kode_order', 'pasien', 'treatment_name', 'category', 'price', 'qty', 'total', 'date'];
        if (!in_array($sortField, $allowedSort, true)) {
            $sortField = 'total';
        }

        return $sortDir === 'asc'
            ? $rows->sortBy($sortField)->values()
            : $rows->sortByDesc($sortField)->values();
    }

    /**
     * Rekap mingguan untuk bulan periode aktif.
     * Pakai filter yang sama dengan baseQuery — pastikan summary card &
     * weekly recap tidak beda hasil.
     */
    public function weeklyRecap(): Collection
    {
        $monthStart = Carbon::create($this->year(), $this->month(), 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Bypass week filter dengan service baru untuk full bulan
        $monthService = new self(array_merge($this->filters, ['week' => null]));
        $rows = $monthService->expandedRawRows();

        return collect([1, 2, 3, 4])->map(function ($w) use ($rows, $monthStart, $monthEnd) {
            $start = $monthStart->copy()->addDays(($w - 1) * 7);
            $end = $w === 4
                ? $monthEnd->copy()
                : $monthStart->copy()->addDays($w * 7 - 1)->endOfDay();
            if ($end->greaterThan($monthEnd)) $end = $monthEnd;

            $bucket = $rows->filter(fn ($r) => $r['week'] === $w);

            $best = null;
            if ($bucket->isNotEmpty()) {
                $g = $bucket->groupBy('treatment_id')
                    ->map(fn ($rows) => [
                        'name' => $rows->first()['treatment_name'],
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
                'total_qty' => (int) $bucket->sum('qty'),
                'total_revenue' => (int) $bucket->sum('total'),
                'best_treatment' => $best,
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

    public function topTreatmentsChart(int $limit = 5): array
    {
        $top = $this->tableRows()
            ->groupBy('treatment_name')
            ->map(fn ($rows) => [
                'name' => $rows->first()['treatment_name'],
                'qty' => (int) $rows->sum('qty'),
            ])
            ->sortByDesc('qty')
            ->take($limit)
            ->values();

        return [
            'labels' => $top->pluck('name')->toArray(),
            'data' => $top->pluck('qty')->toArray(),
        ];
    }

    public function qtyByWeekChart(): array
    {
        $weeks = $this->weeklyRecap();
        return [
            'labels' => $weeks->pluck('label')->toArray(),
            'data' => $weeks->pluck('total_qty')->toArray(),
        ];
    }

    public function tableTotals(): array
    {
        $rows = $this->tableRows();
        return [
            'total_qty' => (int) $rows->sum('qty'),
            'total_revenue' => (int) $rows->sum('total'),
            'transaction_count' => $rows->count(),
        ];
    }

    public static function categoriesOptions(): array
    {
        return TreatmentCategory::orderBy('nama')
            ->where('is_active', true)
            ->pluck('nama', 'treatment_category_id')
            ->toArray();
    }

    public static function treatmentsOptions(): array
    {
        return \App\Models\Treatment::orderBy('nama')
            ->where('is_active', true)
            ->pluck('nama', 'treatment_id')
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
