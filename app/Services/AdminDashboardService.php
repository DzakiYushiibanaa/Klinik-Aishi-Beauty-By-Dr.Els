<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Dokter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pasien;
use App\Models\Product;
use App\Models\Terapis;
use App\Models\Treatment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Service untuk Admin Overview Dashboard.
 *
 * Tujuan: dalam 5–10 detik admin paham kondisi klinik hari ini.
 * Data: pendapatan, appointment, pasien, produk, treatment, payment pending.
 */
class AdminDashboardService
{
    protected const PAID_STATUSES = ['sudah_dibayar'];

    public function __construct()
    {
    }

    /** Tanggal hari ini dalam Carbon. */
    public function today(): Carbon
    {
        return now()->startOfDay();
    }

    public function todayLabel(): string
    {
        return now()->translatedFormat('l, d F Y');
    }

    public function greeting(): string
    {
        $h = now()->hour;
        if ($h < 11) return 'Selamat Pagi';
        if ($h < 15) return 'Selamat Siang';
        if ($h < 19) return 'Selamat Sore';
        return 'Selamat Malam';
    }

    public function adminName(): string
    {
        $user = auth()->user();
        return $user?->name ?? 'Admin';
    }

    /** Summary card hari ini. */
    public function todayStats(): array
    {
        $start = $this->today();
        $end = $start->copy()->endOfDay();

        // Pendapatan hari ini (orders sudah_dibayar, paid_at hari ini, revenue = subtotal − diskon)
        $paidToday = Order::query()
            ->whereIn('status', self::PAID_STATUSES)
            ->whereBetween('paid_at', [$start, $end])
            ->get(['order_id', 'subtotal', 'diskon_total']);

        $revenueToday = (int) $paidToday->sum(fn ($o) => max(0, $o->subtotal - $o->diskon_total));

        // Appointment hari ini (yang dijadwalkan hari ini)
        $apptToday = Appointment::query()
            ->whereBetween('tanggal_jam', [$start, $end])
            ->whereNotIn('status', ['batal'])
            ->count();

        // Pasien hari ini (unik dari appointment hari ini)
        $patientToday = Appointment::query()
            ->whereBetween('tanggal_jam', [$start, $end])
            ->whereNotIn('status', ['batal'])
            ->distinct('user_id')
            ->count('user_id');

        // Produk terjual hari ini (qty dari order_items, item_type=product/produk, order sudah_dibayar hari ini)
        $orderIdsToday = $paidToday->pluck('order_id');
        $productSold = (int) OrderItem::query()
            ->whereIn('order_id', $orderIdsToday)
            ->whereIn('item_type', ['product', 'produk'])
            ->sum('qty');

        // Treatment selesai hari ini (appointment selesai + updated_at hari ini)
        $treatmentDone = Appointment::query()
            ->where('status', 'selesai')
            ->where('jenis', 'treatment')
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        // Pending payment (order menunggu_pembayaran, semua waktu, masih belum expired)
        $pendingPayment = Order::query()
            ->where('status', 'menunggu_pembayaran')
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->count();

        return [
            'revenue_today' => $revenueToday,
            'appointment_today' => $apptToday,
            'patient_today' => $patientToday,
            'product_sold_today' => $productSold,
            'treatment_done_today' => $treatmentDone,
            'pending_payment' => $pendingPayment,
        ];
    }

    /** Pendapatan 7 hari terakhir untuk line chart. */
    public function revenueLastWeek(): array
    {
        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $end = $day->copy()->endOfDay();

            $orders = Order::query()
                ->whereIn('status', self::PAID_STATUSES)
                ->whereBetween('paid_at', [$day, $end])
                ->get(['subtotal', 'diskon_total']);

            $rev = (int) $orders->sum(fn ($o) => max(0, $o->subtotal - $o->diskon_total));

            $labels[] = $day->translatedFormat('D, d M');
            $data[] = $rev;
        }
        return ['labels' => $labels, 'data' => $data];
    }

    /** Jumlah appointment 7 hari terakhir untuk bar chart. */
    public function appointmentsLastWeek(): array
    {
        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $end = $day->copy()->endOfDay();
            $count = Appointment::query()
                ->whereBetween('tanggal_jam', [$day, $end])
                ->whereNotIn('status', ['batal'])
                ->count();
            $labels[] = $day->translatedFormat('D, d M');
            $data[] = $count;
        }
        return ['labels' => $labels, 'data' => $data];
    }

    /** Komposisi pendapatan 30 hari terakhir (Produk/Treatment/Konsultasi). */
    public function revenueComposition(): array
    {
        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        $orderIds = Order::query()
            ->whereIn('status', self::PAID_STATUSES)
            ->whereBetween('paid_at', [$start, $end])
            ->pluck('order_id');

        if ($orderIds->isEmpty()) {
            return ['labels' => ['Produk', 'Treatment', 'Konsultasi'], 'data' => [0, 0, 0]];
        }

        $rows = OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->selectRaw('item_type, SUM(subtotal) as total')
            ->groupBy('item_type')
            ->get();

        $produk = 0;
        $treatment = 0;
        $konsultasi = 0;
        foreach ($rows as $r) {
            $sub = (int) $r->total;
            switch ($r->item_type) {
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

        return [
            'labels' => ['Produk', 'Treatment', 'Konsultasi'],
            'data' => [$produk, $treatment, $konsultasi],
        ];
    }

    /** Jadwal hari ini, max 7. */
    public function todaySchedule(int $limit = 7): array
    {
        $start = $this->today();
        $end = $start->copy()->endOfDay();

        $rows = Appointment::query()
            ->whereBetween('tanggal_jam', [$start, $end])
            ->orderBy('tanggal_jam')
            ->with(['pasien', 'dokter', 'terapis', 'items.treatment', 'items.consultation'])
            ->limit($limit)
            ->get();

        return $rows->map(function ($apt) {
            $tgl = $apt->tanggal_jam instanceof Carbon ? $apt->tanggal_jam : Carbon::parse($apt->tanggal_jam);

            // Service label dari appointment_items (treatment/consultation)
            $serviceParts = [];
            foreach ($apt->items as $i) {
                if ($i->item_type === 'treatment' && $i->treatment) {
                    $serviceParts[] = $i->treatment->nama;
                } elseif ($i->item_type === 'consultation' && $i->consultation) {
                    $serviceParts[] = $i->consultation->nama;
                }
            }
            $service = empty($serviceParts) ? ucfirst($apt->jenis ?? 'Layanan') : implode(', ', $serviceParts);

            // Petugas: terapis kalau treatment, dokter kalau konsultasi
            $petugas = $apt->jenis === 'treatment'
                ? ($apt->terapis?->name ?? '—')
                : ($apt->dokter?->name ?? '—');

            return [
                'time' => $tgl->translatedFormat('H:i'),
                'pasien' => $apt->pasien?->name ?? '—',
                'service' => $service,
                'petugas' => $petugas,
                'status' => $apt->status,
                'status_label' => self::statusLabel($apt->status),
            ];
        })->toArray();
    }

    /** Appointment terbaru (max 5). */
    public function recentAppointments(int $limit = 5): array
    {
        $rows = Appointment::query()
            ->orderByDesc('created_at')
            ->with(['pasien', 'items.treatment', 'items.consultation'])
            ->limit($limit)
            ->get();

        // Ambil order untuk status pembayaran via order_items.appointment_id
        $aptIds = $rows->pluck('appointment_id');
        $orders = OrderItem::query()
            ->whereIn('appointment_id', $aptIds)
            ->with('order:order_id,status')
            ->get()
            ->keyBy('appointment_id');

        return $rows->map(function ($apt) use ($orders) {
            $serviceParts = [];
            foreach ($apt->items as $i) {
                if ($i->item_type === 'treatment' && $i->treatment) {
                    $serviceParts[] = $i->treatment->nama;
                } elseif ($i->item_type === 'consultation' && $i->consultation) {
                    $serviceParts[] = $i->consultation->nama;
                }
            }
            $service = empty($serviceParts) ? ucfirst($apt->jenis ?? 'Layanan') : implode(', ', $serviceParts);

            $orderItem = $orders->get($apt->appointment_id);
            $paymentStatus = $orderItem?->order?->status ?? 'unknown';

            $created = $apt->created_at instanceof Carbon ? $apt->created_at : Carbon::parse($apt->created_at);

            return [
                'pasien' => $apt->pasien?->name ?? '—',
                'kode_appointment' => 'APT-' . str_pad($apt->appointment_id, 4, '0', STR_PAD_LEFT),
                'service' => $service,
                'status' => $apt->status,
                'status_label' => self::statusLabel($apt->status),
                'payment_status' => $paymentStatus,
                'payment_label' => self::paymentLabel($paymentStatus),
                'booking_time' => $created->diffForHumans(),
                'initial' => mb_strtoupper(mb_substr($apt->pasien?->name ?? '?', 0, 1)),
            ];
        })->toArray();
    }

    /** Aktivitas terbaru (mix dari order, appointment, pasien). */
    public function recentActivities(int $limit = 8): array
    {
        $activities = collect();

        // Pembayaran berhasil
        $paid = Order::query()
            ->whereIn('status', self::PAID_STATUSES)
            ->whereNotNull('paid_at')
            ->orderByDesc('paid_at')
            ->limit(5)
            ->with('user')
            ->get(['order_id', 'kode_order', 'order_type', 'paid_at', 'user_id', 'grand_total']);

        foreach ($paid as $o) {
            $activities->push([
                'type' => 'payment',
                'icon_color' => 'emerald',
                'icon' => 'check-circle',
                'title' => 'Pembayaran berhasil',
                'desc' => $o->user?->name . ' • ' . $o->kode_order . ' (Rp ' . number_format($o->grand_total, 0, ',', '.') . ')',
                'time' => Carbon::parse($o->paid_at)->diffForHumans(),
                'sort_at' => Carbon::parse($o->paid_at),
            ]);
        }

        // Appointment selesai
        $done = Appointment::query()
            ->where('status', 'selesai')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->with('pasien')
            ->get(['appointment_id', 'user_id', 'jenis', 'updated_at']);

        foreach ($done as $a) {
            $activities->push([
                'type' => 'appointment_done',
                'icon_color' => 'blue',
                'icon' => 'check',
                'title' => ucfirst($a->jenis) . ' selesai',
                'desc' => $a->pasien?->name ?? '—',
                'time' => Carbon::parse($a->updated_at)->diffForHumans(),
                'sort_at' => Carbon::parse($a->updated_at),
            ]);
        }

        // Pasien baru terdaftar
        $newPatients = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'pasien'))
            ->orderByDesc('created_at')
            ->limit(3)
            ->get(['id', 'name', 'created_at']);

        foreach ($newPatients as $u) {
            $activities->push([
                'type' => 'new_patient',
                'icon_color' => 'amber',
                'icon' => 'user-plus',
                'title' => 'Pasien baru mendaftar',
                'desc' => $u->name,
                'time' => Carbon::parse($u->created_at)->diffForHumans(),
                'sort_at' => Carbon::parse($u->created_at),
            ]);
        }

        // Booking baru
        $newApt = Appointment::query()
            ->orderByDesc('created_at')
            ->limit(3)
            ->with('pasien')
            ->get(['appointment_id', 'user_id', 'jenis', 'created_at']);

        foreach ($newApt as $a) {
            $activities->push([
                'type' => 'new_booking',
                'icon_color' => 'purple',
                'icon' => 'calendar',
                'title' => ucfirst($a->jenis) . ' baru dijadwalkan',
                'desc' => $a->pasien?->name ?? '—',
                'time' => Carbon::parse($a->created_at)->diffForHumans(),
                'sort_at' => Carbon::parse($a->created_at),
            ]);
        }

        return $activities
            ->sortByDesc(fn ($a) => $a['sort_at']->timestamp)
            ->take($limit)
            ->values()
            ->toArray();
    }

    /** Reminders / alerts. */
    public function reminders(): array
    {
        $alerts = [];

        // Produk stok menipis (stok < 5 dan is_active)
        if (Schema::hasColumn('products', 'stok')) {
            $lowStock = Product::query()
                ->where('is_active', true)
                ->where('stok', '<', 5)
                ->orderBy('stok')
                ->limit(5)
                ->get(['product_id', 'nama', 'stok']);

            if ($lowStock->isNotEmpty()) {
                $alerts[] = [
                    'severity' => 'amber',
                    'icon' => 'exclamation-triangle',
                    'title' => 'Stok produk menipis',
                    'desc' => $lowStock->count() . ' produk perlu di-restock (' . $lowStock->take(2)->pluck('nama')->implode(', ') . (($lowStock->count() > 2) ? ', dll.' : '') . ')',
                ];
            }
        }

        // Appointment belum dibayar
        $unpaidApt = Order::query()
            ->where('status', 'menunggu_pembayaran')
            ->whereIn('order_type', ['treatment', 'konsultasi'])
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->count();
        if ($unpaidApt > 0) {
            $alerts[] = [
                'severity' => 'rose',
                'icon' => 'banknotes',
                'title' => 'Appointment menunggu pembayaran',
                'desc' => "{$unpaidApt} pasien belum menyelesaikan pembayaran janji temu.",
            ];
        }

        // Pra-konsul menunggu review (kalau ada model PatientForm)
        if (class_exists(\App\Models\PatientForm::class)) {
            $pending = \App\Models\PatientForm::where('status', 'submitted')->count();
            if ($pending > 0) {
                $alerts[] = [
                    'severity' => 'blue',
                    'icon' => 'clipboard-document-check',
                    'title' => 'Pra-konsultasi menunggu review',
                    'desc' => "{$pending} formulir baru menunggu ditinjau dokter.",
                ];
            }
        }

        // Jadwal hari ini penuh? (>= 20 appointment)
        $todayCount = Appointment::query()
            ->whereBetween('tanggal_jam', [$this->today(), $this->today()->copy()->endOfDay()])
            ->whereNotIn('status', ['batal'])
            ->count();
        if ($todayCount >= 20) {
            $alerts[] = [
                'severity' => 'amber',
                'icon' => 'calendar-days',
                'title' => 'Jadwal hari ini padat',
                'desc' => "{$todayCount} appointment terdaftar untuk hari ini.",
            ];
        }

        return $alerts;
    }

    /** Top performance — Treatment, Produk, Dokter, Terapis terlaris (30 hari terakhir). */
    public function topPerformance(): array
    {
        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        // Treatment terlaris (dari appointment_items selesai + paid)
        $paidAptIds = Order::query()
            ->whereIn('status', self::PAID_STATUSES)
            ->whereBetween('paid_at', [$start, $end])
            ->with('items')
            ->get()
            ->flatMap(fn ($o) => $o->items->pluck('appointment_id'))
            ->filter()
            ->unique();

        $topTreatment = null;
        if ($paidAptIds->isNotEmpty()) {
            $aptItems = \App\Models\AppointmentItem::query()
                ->whereIn('appointment_id', $paidAptIds)
                ->where('item_type', 'treatment')
                ->whereNotNull('treatment_id')
                ->whereHas('appointment', fn ($q) => $q->where('status', 'selesai'))
                ->with('treatment')
                ->get();

            $top = $aptItems->groupBy('treatment_id')
                ->map(fn ($g) => [
                    'name' => $g->first()->treatment?->nama ?? '—',
                    'qty' => (int) $g->sum('qty'),
                ])
                ->sortByDesc('qty')
                ->first();
            if ($top) {
                $topTreatment = $top;
            }
        }

        // Produk terlaris (order_items product, paid 30 hari)
        $paidOrderIds = Order::query()
            ->whereIn('status', self::PAID_STATUSES)
            ->whereBetween('paid_at', [$start, $end])
            ->pluck('order_id');

        $topProduct = null;
        if ($paidOrderIds->isNotEmpty()) {
            $items = OrderItem::query()
                ->whereIn('order_id', $paidOrderIds)
                ->whereIn('item_type', ['product', 'produk'])
                ->whereNotNull('product_id')
                ->with('product')
                ->get();

            $top = $items->groupBy('product_id')
                ->map(fn ($g) => [
                    'name' => $g->first()->product?->nama ?? '—',
                    'qty' => (int) $g->sum('qty'),
                ])
                ->sortByDesc('qty')
                ->first();
            if ($top) {
                $topProduct = $top;
            }
        }

        // Dokter teraktif (appointment selesai 30 hari)
        $topDoctor = null;
        $doctorCount = Appointment::query()
            ->where('status', 'selesai')
            ->whereBetween('updated_at', [$start, $end])
            ->whereNotNull('dokter_id')
            ->selectRaw('dokter_id, COUNT(*) as cnt')
            ->groupBy('dokter_id')
            ->orderByDesc('cnt')
            ->limit(1)
            ->first();
        if ($doctorCount) {
            $name = User::find($doctorCount->dokter_id)?->name ?? '—';
            $topDoctor = ['name' => $name, 'qty' => (int) $doctorCount->cnt];
        }

        // Terapis teraktif
        $topTerapis = null;
        $terapisCount = Appointment::query()
            ->where('status', 'selesai')
            ->whereBetween('updated_at', [$start, $end])
            ->whereNotNull('terapis_id')
            ->selectRaw('terapis_id, COUNT(*) as cnt')
            ->groupBy('terapis_id')
            ->orderByDesc('cnt')
            ->limit(1)
            ->first();
        if ($terapisCount) {
            $name = User::find($terapisCount->terapis_id)?->name ?? '—';
            $topTerapis = ['name' => $name, 'qty' => (int) $terapisCount->cnt];
        }

        return [
            'treatment' => $topTreatment,
            'product' => $topProduct,
            'doctor' => $topDoctor,
            'terapis' => $topTerapis,
        ];
    }

    /** Quick stats — total master data. */
    public function quickStats(): array
    {
        return [
            'total_pasien' => Pasien::count() ?: User::whereHas('roles', fn ($q) => $q->where('name', 'pasien'))->count(),
            'total_produk' => Product::where('is_active', true)->count(),
            'total_treatment' => Treatment::where('is_active', true)->count(),
            'total_dokter' => Dokter::where('status_praktik', 'aktif')->count(),
            'total_terapis' => Terapis::where('status_kerja', 'aktif')->count(),
        ];
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'disetujui' => 'Akan Datang',
            'menunggu_pembayaran' => 'Belum Bayar',
            'selesai' => 'Selesai',
            'batal' => 'Dibatalkan',
            'tidak_hadir' => 'Tidak Hadir',
            default => $status ? ucfirst(str_replace('_', ' ', $status)) : '—',
        };
    }

    public static function paymentLabel(?string $status): string
    {
        return match ($status) {
            'sudah_dibayar' => 'Sudah Dibayar',
            'menunggu_pembayaran' => 'Menunggu',
            'dibatalkan', 'batal' => 'Dibatalkan',
            'expired' => 'Kadaluarsa',
            default => $status ? ucfirst(str_replace('_', ' ', $status)) : '—',
        };
    }
}
