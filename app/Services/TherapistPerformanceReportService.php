<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Order;
use App\Models\Terapis;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service laporan kinerja terapis.
 *
 * Sumber data:
 * - appointments yang punya terapis_id, status='selesai'
 * - appointment_items dengan item_type='treatment' (treatment-level granularity)
 * - Treatment dihitung kalau order terkait (via appointment_id) sudah dibayar
 *
 * Revenue per appointment_item = treatment.harga * qty.
 * Komisi = revenue × commission_rate (default 10%).
 */
class TherapistPerformanceReportService
{
    public const DEFAULT_COMMISSION_RATE = 10;

    /** Status order yang dianggap sudah dibayar. */
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
    public function status(): string { return $this->filters['status'] ?? 'aktif'; }

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

    public static function commissionRateFor(?Terapis $terapis): int
    {
        if (!$terapis) return self::DEFAULT_COMMISSION_RATE;
        $rate = $terapis->commission_rate ?? null;
        return $rate ? (int) $rate : self::DEFAULT_COMMISSION_RATE;
    }

    /**
     * Ambil set appointment_id yang sudah dibayar lewat orders.
     * Di-cache per request agar tidak query berkali-kali.
     */
    protected ?Collection $paidAppointmentIds = null;
    protected ?array $paidAtByAppointmentId = null;

    protected function loadPaidAppointmentIds(Carbon $start, Carbon $end): void
    {
        if ($this->paidAppointmentIds !== null) return;

        $orders = Order::query()
            ->whereIn('status', self::PAID_STATUSES)
            ->whereBetween('paid_at', [$start, $end])
            ->with(['items' => function ($q) {
                $q->whereNotNull('appointment_id');
            }])
            ->get(['order_id', 'paid_at', 'status']);

        $map = [];
        foreach ($orders as $o) {
            foreach ($o->items as $it) {
                if ($it->appointment_id) {
                    $map[$it->appointment_id] = $o->paid_at ?? $o->created_at ?? now();
                }
            }
        }

        $this->paidAtByAppointmentId = $map;
        $this->paidAppointmentIds = collect(array_keys($map));
    }

    /**
     * Ambil appointment_items raw — 1 row per treatment item dari appointment selesai
     * yang sudah dibayar pada periode aktif.
     */
    public function rawRows(): Collection
    {
        $start = $this->periodStart();
        $end = $this->periodEnd();
        $this->loadPaidAppointmentIds($start, $end);

        if ($this->paidAppointmentIds->isEmpty()) {
            return collect();
        }

        $terapisUserId = $this->filters['terapis_user_id'] ?? null;
        $search = $this->filters['search'] ?? null;

        // Ambil semua appointment yang selesai + ada di list paid
        $appointments = Appointment::query()
            ->whereIn('appointment_id', $this->paidAppointmentIds)
            ->where('status', 'selesai')
            ->whereNotNull('terapis_id')
            ->when($terapisUserId, fn ($q) => $q->where('terapis_id', $terapisUserId))
            ->with(['terapis', 'pasien'])
            ->get()
            ->keyBy('appointment_id');

        if ($appointments->isEmpty()) {
            return collect();
        }

        // Ambil appointment_items untuk appointment tersebut
        $items = AppointmentItem::query()
            ->whereIn('appointment_id', $appointments->keys())
            ->where('item_type', 'treatment')
            ->whereNotNull('treatment_id')
            ->with('treatment')
            ->get();

        // Map terapis profile
        $userIds = $appointments->pluck('terapis_id')->unique();
        $terapisProfiles = Terapis::with('user')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $rows = $items->map(function ($item) use ($appointments, $terapisProfiles) {
            $apt = $appointments[$item->appointment_id] ?? null;
            if (!$apt) return null;

            $userId = $apt->terapis_id;
            $terapisProfile = $userId ? ($terapisProfiles[$userId] ?? null) : null;
            $userName = $apt->terapis?->name ?? '—';

            $paidAt = $this->paidAtByAppointmentId[$item->appointment_id] ?? $apt->tanggal_jam ?? now();
            $paidAt = $paidAt instanceof Carbon ? $paidAt : Carbon::parse($paidAt);
            $week = self::weekOfMonth($paidAt);

            $treatment = $item->treatment;
            $price = (int) ($treatment?->harga ?? 0);
            $qty = (int) $item->qty;
            $revenue = $price * $qty;
            $rate = self::commissionRateFor($terapisProfile);

            return [
                'user_id' => $userId,
                'terapis_id' => $terapisProfile?->terapis_id,
                'terapis_name' => $userName,
                'kode_terapis' => $terapisProfile?->kode_terapis ?? '—',
                'status_kerja' => $terapisProfile?->status_kerja ?? 'unknown',
                'appointment_id' => $apt->appointment_id,
                'pasien_user_id' => $apt->user_id,
                'pasien_name' => $apt->pasien?->name ?? '—',
                'treatment_id' => (int) $item->treatment_id,
                'treatment_name' => $treatment?->nama ?? 'Treatment dihapus',
                'qty' => $qty,
                'price' => $price,
                'revenue' => $revenue,
                'commission_rate' => $rate,
                'commission' => (int) round($revenue * $rate / 100),
                'week' => $week,
                'date' => $paidAt,
            ];
        })->filter();

        // Search filter
        if ($search) {
            $q = mb_strtolower($search);
            $rows = $rows->filter(fn ($r) =>
                str_contains(mb_strtolower($r['terapis_name']), $q) ||
                str_contains(mb_strtolower($r['treatment_name']), $q) ||
                str_contains(mb_strtolower($r['pasien_name']), $q)
            );
        }

        return $rows->values();
    }

    /**
     * Aggregasi per terapis (1 row per terapis).
     */
    public function tableRows(): Collection
    {
        $rawRows = $this->rawRows();
        $statusFilter = $this->status();

        $aggregated = $rawRows->groupBy('user_id')->map(function ($rows, $userId) {
            $first = $rows->first();
            $totalQty = (int) $rows->sum('qty');
            $totalRevenue = (int) $rows->sum('revenue');
            $rate = (int) $first['commission_rate'];

            $totalPasien = $rows->pluck('pasien_user_id')->filter()->unique()->count();

            $favorite = $rows->groupBy('treatment_id')
                ->map(fn ($g) => [
                    'name' => $g->first()['treatment_name'],
                    'qty' => (int) $g->sum('qty'),
                ])
                ->sortByDesc('qty')
                ->first();

            $weeksList = $rows->pluck('week')->unique()->sort()->values();

            return [
                'user_id' => (int) $userId,
                'terapis_id' => $first['terapis_id'],
                'terapis_name' => $first['terapis_name'],
                'kode_terapis' => $first['kode_terapis'],
                'status_kerja' => $first['status_kerja'],
                'total_treatment' => $totalQty,
                'total_pasien' => $totalPasien,
                'favorite_treatment' => $favorite['name'] ?? null,
                'total_revenue' => $totalRevenue,
                'commission_rate' => $rate,
                'total_commission' => (int) round($totalRevenue * $rate / 100),
                'weeks_label' => $weeksList->isEmpty() ? '—' : 'Minggu ' . $weeksList->implode(', '),
            ];
        })->values();

        if ($statusFilter !== 'all') {
            $aggregated = $aggregated->filter(fn ($r) => $r['status_kerja'] === $statusFilter)->values();
        }

        $sortField = $this->filters['sort_field'] ?? 'total_commission';
        $sortDir = $this->filters['sort_dir'] ?? 'desc';
        $allowedSort = ['terapis_name', 'total_treatment', 'total_pasien', 'total_revenue', 'total_commission', 'commission_rate'];
        if (!in_array($sortField, $allowedSort, true)) {
            $sortField = 'total_commission';
        }

        return $sortDir === 'asc'
            ? $aggregated->sortBy($sortField)->values()
            : $aggregated->sortByDesc($sortField)->values();
    }

    public function summary(): array
    {
        $rawRows = $this->rawRows();
        $rows = $this->tableRows();

        $totalActive = Terapis::where('status_kerja', 'aktif')->count();
        $totalTreatment = (int) $rawRows->sum('qty');
        $totalRevenue = (int) $rawRows->sum('revenue');
        $totalCommission = (int) $rawRows->sum('commission');

        $best = null;
        if ($rows->isNotEmpty()) {
            $top = $rows->sortByDesc('total_treatment')->first();
            $best = [
                'name' => $top['terapis_name'],
                'count' => $top['total_treatment'],
            ];
        }

        return [
            'total_active' => $totalActive,
            'total_treatment' => $totalTreatment,
            'best_therapist' => $best,
            'total_revenue' => $totalRevenue,
            'total_commission' => $totalCommission,
        ];
    }

    /** Rekap mingguan untuk seluruh bulan periode aktif. */
    public function weeklyRecap(): Collection
    {
        $monthStart = Carbon::create($this->year(), $this->month(), 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Bypass week filter dengan service baru untuk full bulan
        $service = new self(array_merge($this->filters, ['week' => null]));
        $service->loadPaidAppointmentIds($monthStart, $monthEnd);
        $rawRows = $service->rawRows();

        return collect([1, 2, 3, 4])->map(function ($w) use ($rawRows, $monthStart, $monthEnd) {
            $start = $monthStart->copy()->addDays(($w - 1) * 7);
            $end = $w === 4
                ? $monthEnd->copy()
                : $monthStart->copy()->addDays($w * 7 - 1)->endOfDay();
            if ($end->greaterThan($monthEnd)) $end = $monthEnd;

            $bucket = $rawRows->filter(fn ($r) => $r['week'] === $w);

            $bestTherapist = null;
            if ($bucket->isNotEmpty()) {
                $g = $bucket->groupBy('user_id')
                    ->map(fn ($rows) => [
                        'name' => $rows->first()['terapis_name'],
                        'qty' => (int) $rows->sum('qty'),
                    ])
                    ->sortByDesc('qty')
                    ->first();
                $bestTherapist = $g['name'] ?? null;
            }

            return [
                'week' => $w,
                'label' => "Minggu $w",
                'range' => $start->translatedFormat('d M') . ' – ' . $end->translatedFormat('d M'),
                'total_qty' => (int) $bucket->sum('qty'),
                'total_revenue' => (int) $bucket->sum('revenue'),
                'total_commission' => (int) $bucket->sum('commission'),
                'best_therapist' => $bestTherapist,
            ];
        });
    }

    public function topTherapistsChart(int $limit = 5): array
    {
        $top = $this->tableRows()->sortByDesc('total_treatment')->take($limit)->values();
        return [
            'labels' => $top->pluck('terapis_name')->toArray(),
            'data' => $top->pluck('total_treatment')->toArray(),
        ];
    }

    public function revenueByTherapistChart(int $limit = 8): array
    {
        $top = $this->tableRows()->sortByDesc('total_revenue')->take($limit)->values();
        return [
            'labels' => $top->pluck('terapis_name')->toArray(),
            'data' => $top->pluck('total_revenue')->toArray(),
        ];
    }

    public function commissionByTherapistChart(int $limit = 8): array
    {
        $top = $this->tableRows()->sortByDesc('total_commission')->take($limit)->values();
        return [
            'labels' => $top->pluck('terapis_name')->toArray(),
            'data' => $top->pluck('total_commission')->toArray(),
        ];
    }

    public function tableTotals(): array
    {
        $rows = $this->tableRows();
        return [
            'total_treatment' => (int) $rows->sum('total_treatment'),
            'total_pasien' => (int) $rows->sum('total_pasien'),
            'total_revenue' => (int) $rows->sum('total_revenue'),
            'total_commission' => (int) $rows->sum('total_commission'),
            'therapist_count' => $rows->count(),
        ];
    }

    public static function therapistOptions(): array
    {
        return Terapis::with('user')
            ->whereHas('user')
            ->orderBy('terapis_id')
            ->get()
            ->mapWithKeys(fn ($t) => [
                $t->user_id => ($t->user?->name ?? 'Tanpa Nama') . ' (' . $t->kode_terapis . ')',
            ])
            ->toArray();
    }

    public static function statusOptions(): array
    {
        return [
            'aktif' => 'Aktif',
            'nonaktif' => 'Non-Aktif',
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
