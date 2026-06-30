<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Dokter;
use App\Models\Order;
use App\Support\TreatmentCommissionCalculator;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service laporan kinerja dokter.
 *
 * Sumber data:
 * - appointments (dokter_id, status='selesai')
 * - appointment_items (item_type='consultation' / 'treatment')
 * - orders (status='sudah_dibayar') → filter pembayaran
 * - consultations (Konsultasi Awal vs Konsultasi Lanjutan via nama)
 * - treatments (harga × qty = revenue treatment)
 *
 * Komisi:
 * - Konsultasi: 15%
 * - Treatment: 10%
 *
 * Hanya appointment selesai dengan order sudah dibayar yang dihitung.
 */
class DoctorPerformanceReportService
{
    public const COMMISSION_RATE_KONSULTASI = 15;
    public const COMMISSION_RATE_TREATMENT = 10;
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

    /** Tentukan apakah konsultasi awal atau lanjutan dari nama. */
    public static function consultationCategory(?string $nama): string
    {
        if (!$nama) return 'lanjutan';
        $lower = mb_strtolower($nama);
        if (str_contains($lower, 'awal')) return 'awal';
        return 'lanjutan';
    }

    /** Cache appointment_id → paid_at dari order sudah dibayar. */
    protected ?array $paidAtByAppointmentId = null;

    protected function loadPaidAppointmentIds(Carbon $start, Carbon $end): array
    {
        if ($this->paidAtByAppointmentId !== null) return $this->paidAtByAppointmentId;

        $orders = Order::query()
            ->whereIn('status', self::PAID_STATUSES)
            ->whereBetween('paid_at', [$start, $end])
            ->with(['items' => fn ($q) => $q->whereNotNull('appointment_id')])
            ->get(['order_id', 'paid_at']);

        $map = [];
        foreach ($orders as $o) {
            foreach ($o->items as $it) {
                if ($it->appointment_id) {
                    $map[$it->appointment_id] = $o->paid_at ?? $o->created_at ?? now();
                }
            }
        }
        $this->paidAtByAppointmentId = $map;
        return $map;
    }

    /**
     * Ambil semua row mentah (1 row per item: consultation atau treatment)
     * dari appointment selesai + order sudah dibayar + ada dokter_id.
     *
     * Data konsultasi diambil dari kombinasi:
     * - appointment_items (jika ada → consultation_id memberi tahu Awal/Lanjutan)
     * - order_items dengan item_type='konsultasi' (fallback untuk data legacy
     *   tanpa appointment_items, gunakan subtotal sebagai revenue)
     *
     * Data treatment diambil dari appointment_items.item_type='treatment'
     * dengan harga = treatment.harga × qty.
     */
    public function rawRows(): Collection
    {
        $start = $this->periodStart();
        $end = $this->periodEnd();
        $paidMap = $this->loadPaidAppointmentIds($start, $end);

        if (empty($paidMap)) return collect();

        $dokterUserId = $this->filters['dokter_user_id'] ?? null;
        $search = $this->filters['search'] ?? null;

        // Appointment selesai + ada di paid map + ada dokter
        $appointments = Appointment::query()
            ->whereIn('appointment_id', array_keys($paidMap))
            ->where('status', 'selesai')
            ->with(['dokter', 'pasien'])
            ->get()
            ->keyBy('appointment_id');

        if ($appointments->isEmpty()) return collect();

        // appointment_items (konsultasi + treatment) — jika ada
        $appointmentItems = AppointmentItem::query()
            ->whereIn('appointment_id', $appointments->keys())
            ->whereIn('item_type', ['consultation', 'treatment'])
            ->with(['consultation', 'treatment', 'handler'])
            ->get();

        // Map dokter profile
        $handlerDoctorIds = $appointmentItems
            ->where('item_type', 'treatment')
            ->where('handled_by_type', 'dokter')
            ->pluck('handled_by_id');

        $userIds = $appointments->pluck('dokter_id')
            ->merge($handlerDoctorIds)
            ->filter()
            ->unique();
        $dokterProfiles = Dokter::with('user')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $rows = collect();

        // === 1. Treatment dari appointment_items ===
        foreach (
            $appointmentItems
                ->where('item_type', 'treatment')
                ->where('handled_by_type', 'dokter')
                ->whereNotNull('handled_by_id') as $item
        ) {
            if ($dokterUserId && (int) $item->handled_by_id !== (int) $dokterUserId) {
                continue;
            }

            $apt = $appointments[$item->appointment_id] ?? null;
            if (!$apt) continue;

            $price = (int) ($item->treatment?->harga ?? 0);
            $qty = (int) $item->qty;
            $commission = (int) ($item->commission_amount ?: TreatmentCommissionCalculator::forItem($item));

            $rows->push($this->buildRow(
                apt: $apt,
                dokterProfiles: $dokterProfiles,
                paidMap: $paidMap,
                type: 'treatment',
                serviceName: $item->treatment?->nama ?? 'Treatment dihapus',
                qty: $qty,
                price: $price,
                consultationCategory: null,
                handlerUserId: (int) $item->handled_by_id,
                handlerName: $item->handler?->name,
                overrideCommission: $commission,
            ));
        }

        // === 2. Konsultasi dari appointment_items (jika ada) ===
        $consultationFromAppItems = $appointmentItems->where('item_type', 'consultation');
        $aptsWithAppointmentConsultation = $consultationFromAppItems->pluck('appointment_id')->unique();

        foreach ($consultationFromAppItems as $item) {
            $apt = $appointments[$item->appointment_id] ?? null;
            if (!$apt) continue;
            if (!$apt->dokter_id) continue;
            if ($dokterUserId && (int) $apt->dokter_id !== (int) $dokterUserId) continue;

            $consultName = $item->consultation?->nama ?? 'Konsultasi';
            $rows->push($this->buildRow(
                apt: $apt,
                dokterProfiles: $dokterProfiles,
                paidMap: $paidMap,
                type: 'consultation',
                serviceName: $consultName,
                qty: (int) $item->qty,
                price: (int) ($item->consultation?->harga ?? 0),
                consultationCategory: self::consultationCategory($consultName),
            ));
        }

        // === 3. Konsultasi dari order_items (fallback untuk apt tanpa app_items) ===
        $aptIdsKonsultasi = $appointments->filter(fn ($a) => $a->jenis === 'konsultasi')
            ->keys()
            ->diff($aptsWithAppointmentConsultation)
            ->values();

        if ($aptIdsKonsultasi->isNotEmpty()) {
            $orderItemsKonsul = \App\Models\OrderItem::query()
                ->whereIn('appointment_id', $aptIdsKonsultasi)
                ->where('item_type', 'konsultasi')
                ->get();

            foreach ($orderItemsKonsul as $oi) {
                $apt = $appointments[$oi->appointment_id] ?? null;
                if (!$apt) continue;
                if (!$apt->dokter_id) continue;
                if ($dokterUserId && (int) $apt->dokter_id !== (int) $dokterUserId) continue;

                // Detect awal/lanjutan dari harga: 125000 default awal, 100000 lanjutan
                $price = (int) $oi->harga_satuan ?: ((int) $oi->subtotal / max(1, (int) $oi->qty));
                $category = $price >= 110000 ? 'awal' : 'lanjutan';

                $rows->push($this->buildRow(
                    apt: $apt,
                    dokterProfiles: $dokterProfiles,
                    paidMap: $paidMap,
                    type: 'consultation',
                    serviceName: $category === 'awal' ? 'Konsultasi Awal' : 'Konsultasi Lanjutan',
                    qty: (int) $oi->qty,
                    price: (int) $price,
                    consultationCategory: $category,
                    overrideRevenue: (int) $oi->subtotal,  // pakai subtotal langsung
                ));
            }
        }

        if ($search) {
            $q = mb_strtolower($search);
            $rows = $rows->filter(fn ($r) =>
                str_contains(mb_strtolower($r['dokter_name']), $q) ||
                str_contains(mb_strtolower($r['service_name']), $q) ||
                str_contains(mb_strtolower($r['pasien_name']), $q)
            );
        }

        return $rows->values();
    }

    /** Build a single row dict. */
    protected function buildRow(
        $apt,
        Collection $dokterProfiles,
        array $paidMap,
        string $type,
        string $serviceName,
        int $qty,
        int $price,
        ?string $consultationCategory,
        ?int $overrideRevenue = null,
        ?int $handlerUserId = null,
        ?string $handlerName = null,
        ?int $overrideCommission = null,
    ): array {
        $userId = $handlerUserId ?? $apt->dokter_id;
        $dokterProfile = $userId ? ($dokterProfiles[$userId] ?? null) : null;
        $overrideDoctorName = $handlerName;
        $userName = $apt->dokter?->name ?? '—';

        $paidAt = $paidMap[$apt->appointment_id] ?? $apt->tanggal_jam ?? now();
        $paidAt = $paidAt instanceof Carbon ? $paidAt : Carbon::parse($paidAt);

        $revenue = $overrideRevenue ?? ($price * $qty);
        $rate = $type === 'consultation'
            ? self::COMMISSION_RATE_KONSULTASI
            : self::COMMISSION_RATE_TREATMENT;

        return [
            'user_id' => $userId,
            'dokter_id' => $dokterProfile?->dokter_id,
            'dokter_name' => $overrideDoctorName ?? $userName,
            'kode_dokter' => $dokterProfile?->kode_dokter ?? '—',
            'status_praktik' => $dokterProfile?->status_praktik ?? 'unknown',
            'appointment_id' => $apt->appointment_id,
            'pasien_user_id' => $apt->user_id,
            'pasien_name' => $apt->pasien?->name ?? '—',
            'item_type' => $type,
            'consultation_category' => $consultationCategory,
            'service_name' => $serviceName,
            'qty' => $qty,
            'price' => $price,
            'revenue' => $revenue,
            'commission_rate' => $rate,
            'commission' => $overrideCommission ?? (int) round($revenue * $rate / 100),
            'week' => self::weekOfMonth($paidAt),
            'date' => $paidAt,
        ];
    }

    /**
     * Aggregasi per dokter (1 row per dokter).
     */
    public function tableRows(): Collection
    {
        $rawRows = $this->rawRows();
        $statusFilter = $this->status();

        $aggregated = $rawRows->groupBy('user_id')->map(function ($rows, $userId) {
            $first = $rows->first();

            $consultationRows = $rows->where('item_type', 'consultation');
            $treatmentRows = $rows->where('item_type', 'treatment');

            $konsultasiAwal = (int) $consultationRows->where('consultation_category', 'awal')->sum('qty');
            $konsultasiLanjutan = (int) $consultationRows->where('consultation_category', 'lanjutan')->sum('qty');
            $totalKonsultasi = $konsultasiAwal + $konsultasiLanjutan;

            $totalTreatment = (int) $treatmentRows->sum('qty');

            $revenueKonsultasi = (int) $consultationRows->sum('revenue');
            $revenueTreatment = (int) $treatmentRows->sum('revenue');
            $totalRevenue = $revenueKonsultasi + $revenueTreatment;

            $komisiKonsultasi = (int) $consultationRows->sum('commission');
            $komisiTreatment = (int) $treatmentRows->sum('commission');
            $totalKomisi = $komisiKonsultasi + $komisiTreatment;

            $totalPasien = $rows->pluck('pasien_user_id')->filter()->unique()->count();

            // Treatment ditangani: list treatment dengan qty (sorted desc)
            $treatmentList = $treatmentRows
                ->groupBy('service_name')
                ->map(fn ($g) => [
                    'name' => $g->first()['service_name'],
                    'qty' => (int) $g->sum('qty'),
                ])
                ->sortByDesc('qty')
                ->values()
                ->toArray();

            $weeksList = $rows->pluck('week')->unique()->sort()->values();

            return [
                'user_id' => (int) $userId,
                'dokter_id' => $first['dokter_id'],
                'dokter_name' => $first['dokter_name'],
                'kode_dokter' => $first['kode_dokter'],
                'status_praktik' => $first['status_praktik'],
                'konsultasi_awal' => $konsultasiAwal,
                'konsultasi_lanjutan' => $konsultasiLanjutan,
                'total_konsultasi' => $totalKonsultasi,
                'total_treatment' => $totalTreatment,
                'treatment_list' => $treatmentList,
                'total_pasien' => $totalPasien,
                'revenue_konsultasi' => $revenueKonsultasi,
                'revenue_treatment' => $revenueTreatment,
                'total_revenue' => $totalRevenue,
                'komisi_konsultasi' => $komisiKonsultasi,
                'komisi_treatment' => $komisiTreatment,
                'total_komisi' => $totalKomisi,
                'weeks_label' => $weeksList->isEmpty() ? '—' : 'Minggu ' . $weeksList->implode(', '),
            ];
        })->values();

        if ($statusFilter !== 'all') {
            $aggregated = $aggregated->filter(fn ($r) => $r['status_praktik'] === $statusFilter)->values();
        }

        $sortField = $this->filters['sort_field'] ?? 'total_komisi';
        $sortDir = $this->filters['sort_dir'] ?? 'desc';
        $allowedSort = [
            'dokter_name', 'konsultasi_awal', 'konsultasi_lanjutan', 'total_konsultasi',
            'total_treatment', 'total_pasien', 'total_revenue', 'total_komisi',
        ];
        if (!in_array($sortField, $allowedSort, true)) $sortField = 'total_komisi';

        return $sortDir === 'asc'
            ? $aggregated->sortBy($sortField)->values()
            : $aggregated->sortByDesc($sortField)->values();
    }

    public function summary(): array
    {
        $rawRows = $this->rawRows();
        $rows = $this->tableRows();

        $totalActive = Dokter::where('status_praktik', 'aktif')->count();

        $totalKonsultasi = (int) $rawRows->where('item_type', 'consultation')->sum('qty');
        $totalTreatment = (int) $rawRows->where('item_type', 'treatment')->sum('qty');
        $totalRevenue = (int) $rawRows->sum('revenue');
        $totalCommission = (int) $rawRows->sum('commission');

        $best = null;
        if ($rows->isNotEmpty()) {
            $top = $rows->sortByDesc(fn ($r) => $r['total_konsultasi'] + $r['total_treatment'])->first();
            $best = [
                'name' => $top['dokter_name'],
                'count' => $top['total_konsultasi'] + $top['total_treatment'],
            ];
        }

        return [
            'total_active' => $totalActive,
            'total_konsultasi' => $totalKonsultasi,
            'total_treatment' => $totalTreatment,
            'best_doctor' => $best,
            'total_revenue' => $totalRevenue,
            'total_commission' => $totalCommission,
        ];
    }

    public function weeklyRecap(): Collection
    {
        $monthStart = Carbon::create($this->year(), $this->month(), 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Bypass week filter
        $service = new self(array_merge($this->filters, ['week' => null]));
        $rawRows = $service->rawRows();

        return collect([1, 2, 3, 4])->map(function ($w) use ($rawRows, $monthStart, $monthEnd) {
            $start = $monthStart->copy()->addDays(($w - 1) * 7);
            $end = $w === 4
                ? $monthEnd->copy()
                : $monthStart->copy()->addDays($w * 7 - 1)->endOfDay();
            if ($end->greaterThan($monthEnd)) $end = $monthEnd;

            $bucket = $rawRows->filter(fn ($r) => $r['week'] === $w);

            $konsAwal = (int) $bucket->where('item_type', 'consultation')->where('consultation_category', 'awal')->sum('qty');
            $konsLanjutan = (int) $bucket->where('item_type', 'consultation')->where('consultation_category', 'lanjutan')->sum('qty');
            $treatments = (int) $bucket->where('item_type', 'treatment')->sum('qty');

            $bestDoctor = null;
            if ($bucket->isNotEmpty()) {
                $g = $bucket->groupBy('user_id')
                    ->map(fn ($rows) => [
                        'name' => $rows->first()['dokter_name'],
                        'qty' => (int) $rows->sum('qty'),
                    ])
                    ->sortByDesc('qty')
                    ->first();
                $bestDoctor = $g['name'] ?? null;
            }

            return [
                'week' => $w,
                'label' => "Minggu $w",
                'range' => $start->translatedFormat('d M') . ' – ' . $end->translatedFormat('d M'),
                'konsultasi_awal' => $konsAwal,
                'konsultasi_lanjutan' => $konsLanjutan,
                'total_treatment' => $treatments,
                'total_revenue' => (int) $bucket->sum('revenue'),
                'total_commission' => (int) $bucket->sum('commission'),
                'best_doctor' => $bestDoctor,
            ];
        });
    }

    /** Top 5 dokter berdasarkan total layanan (konsultasi + treatment). */
    public function topDoctorsChart(int $limit = 5): array
    {
        $top = $this->tableRows()
            ->sortByDesc(fn ($r) => $r['total_konsultasi'] + $r['total_treatment'])
            ->take($limit)
            ->values();

        return [
            'labels' => $top->pluck('dokter_name')->toArray(),
            'data' => $top->map(fn ($r) => $r['total_konsultasi'] + $r['total_treatment'])->toArray(),
        ];
    }

    public function revenueByDoctorChart(int $limit = 8): array
    {
        $top = $this->tableRows()->sortByDesc('total_revenue')->take($limit)->values();
        return [
            'labels' => $top->pluck('dokter_name')->toArray(),
            'data' => $top->pluck('total_revenue')->toArray(),
        ];
    }

    public function commissionByDoctorChart(int $limit = 8): array
    {
        $top = $this->tableRows()->sortByDesc('total_komisi')->take($limit)->values();
        return [
            'labels' => $top->pluck('dokter_name')->toArray(),
            'data' => $top->pluck('total_komisi')->toArray(),
        ];
    }

    public function tableTotals(): array
    {
        $rows = $this->tableRows();
        return [
            'konsultasi_awal' => (int) $rows->sum('konsultasi_awal'),
            'konsultasi_lanjutan' => (int) $rows->sum('konsultasi_lanjutan'),
            'total_konsultasi' => (int) $rows->sum('total_konsultasi'),
            'total_treatment' => (int) $rows->sum('total_treatment'),
            'total_pasien' => (int) $rows->sum('total_pasien'),
            'total_revenue' => (int) $rows->sum('total_revenue'),
            'komisi_konsultasi' => (int) $rows->sum('komisi_konsultasi'),
            'komisi_treatment' => (int) $rows->sum('komisi_treatment'),
            'total_komisi' => (int) $rows->sum('total_komisi'),
            'doctor_count' => $rows->count(),
        ];
    }

    public static function doctorOptions(): array
    {
        return Dokter::with('user')
            ->whereHas('user')
            ->orderBy('dokter_id')
            ->get()
            ->mapWithKeys(fn ($d) => [
                $d->user_id => ($d->user?->name ?? 'Tanpa Nama') . ' (' . $d->kode_dokter . ')',
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
