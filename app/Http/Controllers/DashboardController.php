<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pasien;
use App\Models\PatientForm;
use App\Models\RekamMedis;
use App\Models\User;
use App\Services\Members\ConsultationMembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        app(ConsultationMembershipService::class)->syncForUser($user);
        $user->load('pasienProfile');

        $pasienId = Pasien::where('user_id', $user->id)->value('pasien_id');

        // ── Data Janji Temu ──
        $appointments = Appointment::where('user_id', $user->id)
            ->with(['dokter', 'terapis', 'items.handler', 'items.treatment', 'items.consultation', 'orderItems.product', 'orderItems.treatment', 'orderItems.consultation'])
            ->orderBy('tanggal_jam', 'desc')
            ->get();

        // ── Data Jadwal Praktik Dokter (untuk modal reschedule) ──
        $jadwals = \App\Models\JadwalPraktik::where('is_active', true)->get();

        // Mapping appointment_id => collection of orders (untuk tab Janji Temu)
        $appointmentOrderMap = OrderItem::whereIn('appointment_id', $appointments->pluck('appointment_id'))
            ->with(['order.items.product', 'order.items.treatment', 'order.items.consultation'])
            ->get()
            ->groupBy('appointment_id')
            ->map(function($items) {
                return $items->pluck('order')->unique('order_id')->filter();
            });

        // ── Data Pra-Konsultasi ──
        $praKonsuls = PatientForm::where('pasien_id', $pasienId)
            ->with(['dokter', 'reviewer', 'rekomendasiItems.product', 'rekomendasiItems.treatment'])
            ->orderBy('created_at', 'desc')
            ->get();

        // ── Data Pesanan (semua tipe) ──
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product', 'items.treatment'])
            ->orderBy('created_at', 'desc')
            ->get();

        // ── Overview: Jadwal Mendatang ──
        $upcomingAppointments = Appointment::where('user_id', $user->id)
            ->whereIn('status', ['disetujui'])
            ->where('tanggal_jam', '>=', now())
            ->with(['dokter', 'terapis', 'items.handler', 'items.treatment'])
            ->orderBy('tanggal_jam', 'asc')
            ->limit(3)
            ->get();

        // ── Rekam Medis ──
        $rekamMedis = RekamMedis::where('pasien_id', $user->id)
            ->with(['appointment.items.treatment', 'dokter', 'terapis'])
            ->orderBy('created_at', 'desc')
            ->get();

        // ── Stats Overview ──
        $stats = [
            'total_appointments' => $appointments->count(),
            'upcoming' => $appointments->whereIn('status', ['disetujui', 'menunggu_pembayaran'])->count(),
            'total_orders' => $orders->count(),
            'active_orders' => $orders->where('order_type', 'produk')->whereIn('status', ['menunggu_pembayaran', 'sudah_dibayar'])->whereNotIn('shipping_status', ['selesai', 'dibatalkan'])->count(),
            'total_rekam_medis' => $rekamMedis->count(),
        ];

        // ── Sidebar Badges ──
        $badges = [
            'appointment' => $appointments->whereIn('status', ['disetujui', 'menunggu_pembayaran'])->count(),
            'pra_konsul' => $praKonsuls->whereIn('status', ['baru', 'submitted', 'menunggu', 'ditinjau', 'in_review', 'diproses', 'reviewed', 'direkomendasikan'])->count(),
            'pesanan' => $orders->where('order_type', 'produk')->whereIn('status', ['menunggu_pembayaran', 'sudah_dibayar'])->whereNotIn('shipping_status', ['selesai', 'dibatalkan'])->count(),
        ];

        // ── Pending Payments ──
        // Termasuk order menunggu pembayaran DAN order dengan pengajuan
        // pembatalan (cancel_requested) — pasien tetap harus melihat
        // statusnya sampai admin menyetujui/menolak.
        $pendingPayments = [];
        $pendingOrders = $orders->whereIn('status', ['menunggu_pembayaran', 'cancel_requested'])
            ->filter(function ($order) {
                return is_null($order->expired_at) || $order->expired_at > now();
            });
        
        foreach ($pendingOrders as $order) {
            $isTreatment = $order->items->contains(function ($item) {
                return in_array($item->item_type, ['treatment', 'consultation']);
            });

            if ($isTreatment) {
                // Cari appointment terkait
                $appointmentId = $order->items->first()->appointment_id;
                $appointment = $appointments->where('appointment_id', $appointmentId)->first();
                if ($appointment) {
                    $pendingPayments[] = [
                        'type' => 'treatment',
                        'order' => $order,
                        'appointment' => $appointment,
                    ];
                }
            } else {
                $pendingPayments[] = [
                    'type' => 'product',
                    'order' => $order,
                ];
            }
        }

        // ── Next Action Logic (Lanjutkan Aktivitas) ──
        $nextAction = null;
        $reviewedKonsul = $praKonsuls->whereIn('status', ['reviewed', 'direkomendasikan', 'selesai'])->first();
        $pendingAppointment = $appointments->where('status', 'disetujui')->first();
        if ($reviewedKonsul) {
            $nextAction = [
                'type' => 'recommendation',
                'title' => 'Rekomendasi Dokter Tersedia',
                'description' => 'Dokter telah meninjau kondisi kulit Anda dan memberikan rekomendasi perawatan.',
                'cta_text' => 'Lihat Rekomendasi',
                'cta_tab' => 'prakonsul',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                'color' => 'blue'
            ];
        } elseif ($pendingAppointment) {
            $nextAction = [
                'type' => 'appointment',
                'title' => 'Janji Temu Disetujui',
                'description' => 'Jadwal janji temu Anda telah disetujui.',
                'cta_text' => 'Lihat Jadwal',
                'cta_tab' => 'appointment',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                'color' => 'amber'
            ];
        } else {
            $nextAction = [
                'type' => 'empty',
                'title' => 'Selamat datang di Aishi Beauty!',
                'description' => 'Awali perjalanan kulit sehatmu dengan konsultasi dokter secara gratis.',
                'cta_text' => 'Mulai Pra-Konsultasi',
                'cta_link' => route('pra.create'),
                'secondary_cta_text' => 'Eksplor Perawatan',
                'secondary_cta_link' => route('treatment.index'),
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
                'color' => 'emerald'
            ];
        }

        return view('dashboard', compact(
            'user', 'appointments', 'appointmentOrderMap', 'praKonsuls', 'orders',
            'upcomingAppointments', 'rekamMedis', 'stats', 'badges', 'nextAction', 'jadwals', 'pendingPayments'
        ));
    }

    // ==========================================
    // API WILAYAH dengan CACHE (RAJAONGKIR)
    // ==========================================

    public function getProvinces()
    {
        try {
            $provinces = Cache::remember('rajaongkir_provinces', now()->addDays(7), function () {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'key'    => config('rajaongkir.api_key'),
                ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

                if ($response->successful()) {
                    return $response->json()['data'] ?? [];
                }
                
                return [];
            });

            Log::info('Provinces loaded from cache', ['count' => count($provinces)]);
            return response()->json($provinces);
            
        } catch (\Exception $e) {
            Log::error('Error fetching provinces: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCities($provinceId)
    {
        try {
            $cacheKey = "rajaongkir_cities_{$provinceId}";
            
            $cities = Cache::remember($cacheKey, now()->addDays(7), function () use ($provinceId) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'key'    => config('rajaongkir.api_key'),
                ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$provinceId}");

                if ($response->successful()) {
                    return $response->json()['data'] ?? [];
                }
                
                return [];
            });

            Log::info('Cities loaded', [
                'province_id' => $provinceId,
                'count' => count($cities),
                'from_cache' => Cache::has($cacheKey)
            ]);
            
            return response()->json($cities);
            
        } catch (\Exception $e) {
            Log::error('Error fetching cities: ' . $e->getMessage(), [
                'province_id' => $provinceId
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getDistricts($cityId)
    {
        try {
            $cacheKey = "rajaongkir_districts_{$cityId}";
            
            $districts = Cache::remember($cacheKey, now()->addDays(7), function () use ($cityId) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'key'    => config('rajaongkir.api_key'),
                ])->get("https://rajaongkir.komerce.id/api/v1/destination/district/{$cityId}");

                if ($response->successful()) {
                    return $response->json()['data'] ?? [];
                }
                
                return [];
            });

            Log::info('Districts loaded', [
                'city_id' => $cityId,
                'count' => count($districts),
                'from_cache' => Cache::has($cacheKey)
            ]);
            
            return response()->json($districts);
            
        } catch (\Exception $e) {
            Log::error('Error fetching districts: ' . $e->getMessage(), [
                'city_id' => $cityId
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function clearLocationCache()
    {
        Cache::forget('rajaongkir_provinces');
        
        $keys = Cache::get('rajaongkir_cache_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        return back()->with('success', 'Cache wilayah berhasil dihapus.');
    }

    // ==========================================
    // UPDATE PROFILE
    // ==========================================
    
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'alamat'        => 'nullable|string|max:500',
            'province_id'   => 'nullable|string',
            'city_id'       => 'nullable|string',
            'district_id'   => 'nullable|string',
            'provinsi'      => 'nullable|string|max:100',
            'kota'          => 'nullable|string|max:100',
            'district_name' => 'nullable|string|max:100',
            'kode_pos'      => 'nullable|string|max:10',
            'foto'          => 'nullable|image|mimes:jpeg,jpg,png|max:1024', 
        ]);

        try {
            DB::transaction(function () use ($request, $user) {
                $user->update([
                    'name'          => $request->name,
                    'phone'         => $request->phone,
                ]);

                $fotoPath = $user->pasienProfile?->foto_profil_path; 
                
                if ($request->hasFile('foto')) {
                    if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                        Storage::disk('public')->delete($fotoPath);
                    }
                    $fotoPath = $request->file('foto')->store('pasien-photos', 'public');
                }

                $user->pasienProfile()->updateOrCreate(
                    ['user_id' => $user->id], 
                    [
                        'alamat'           => $request->alamat,
                        'kode_pos'         => $request->kode_pos,
                        'foto_profil_path' => $fotoPath,
                        'province_id'      => $request->province_id,
                        'city_id'          => $request->city_id,
                        'district_id'      => $request->district_id,
                        'provinsi'         => $request->provinsi,
                        'kota'             => $request->kota,
                        'district_name'    => $request->district_name,
                        'jenis_kelamin'    => $request->jenis_kelamin,
                        'tanggal_lahir'    => $request->tanggal_lahir,
                    ]
                );
            });

            Log::info('Profile updated successfully', ['user_id' => $user->id]);

            // Setelah profil disimpan, kalau user tadi di-redirect ke sini karena
            // profil belum lengkap saat mau booking / checkout, antar dia balik
            // ke flow itu. Cart session TIDAK disentuh sama sekali jadi produk
            // di keranjang tetap aman.
            $user->refresh();
            $intended = session('profile_required_redirect');

            if ($intended) {
                $isCheckoutFlow = str_contains($intended, 'checkout') || str_contains($intended, 'keranjang') || str_contains($intended, 'cart');
                $isAppointmentFlow = str_contains($intended, 'appointment') || str_contains($intended, 'janji-temu');

                $pasienProfile  = $user->pasienProfile()->first();
                $hasContact     = $user->phone && $pasienProfile?->tanggal_lahir;
                $hasShipping    = $pasienProfile
                    && !empty($pasienProfile->alamat)
                    && !empty($pasienProfile->district_id);

                $canResume = match (true) {
                    $isCheckoutFlow    => $hasContact && $hasShipping,
                    $isAppointmentFlow => $hasContact,
                    default            => $hasContact,
                };

                if ($canResume) {
                    session()->forget('profile_required_redirect');

                    $msg = $isCheckoutFlow
                        ? 'Profil berhasil diperbarui. Silakan lanjutkan checkout — produk Anda masih ada di keranjang.'
                        : 'Profil berhasil diperbarui. Silakan lanjutkan janji temu Anda.';

                    return redirect($intended)->with('success', $msg);
                }
            }

            return back()->with('success', 'Profil berhasil diperbarui.');
            
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);
            return back()->with('error', 'Gagal memperbarui profil. Silakan coba lagi.');
        }
    }

    // ==========================================
    // ORDER ACTIONS
    // ==========================================

    public function markOrderCompleted(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Tindakan tidak diizinkan.');
        }

        if ($order->shipping_status !== 'dikirim') {
            return back()->with('error', 'Pesanan belum dikirim atau sudah selesai.');
        }

        $order->update([
            'shipping_status' => 'selesai'
        ]);

        Log::info('Order completed by user', ['order_id' => $order->order_id, 'user_id' => Auth::id()]);

        return back()->with('success', 'Terima kasih, pesanan Anda telah ditandai selesai.');
    }

    /**
     * Pasien mengajukan pembatalan pesanan.
     *
     * Pesanan TIDAK langsung dibatalkan — status berubah menjadi
     * `cancel_requested` dan menunggu konfirmasi admin/apoteker.
     * Stok belum dikembalikan; restock baru terjadi saat admin
     * menyetujui pengajuan (lihat OrdersTable di Filament).
     */
    public function cancelOrder(Order $order)
    {
        // 1. Validasi Pemilik
        if ($order->user_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Validasi Status
        if (!in_array($order->status, ['menunggu_pembayaran', 'sudah_dibayar'])) {
            return back()->with('error', 'Pesanan ini tidak dapat dibatalkan lagi.');
        }

        try {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => 'cancel_requested',
                ]);

                // Notifikasi admin/apoteker bahwa ada pengajuan pembatalan
                // yang perlu ditinjau.
                $penerimaNotif = \App\Models\User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['super_admin', 'admin', 'apoteker']);
                })->get();

                foreach ($penerimaNotif as $penerima) {
                    try {
                        DB::table('notifications')->insert([
                            'id' => \Illuminate\Support\Str::uuid()->toString(),
                            'type' => 'Filament\\Notifications\\DatabaseNotification',
                            'notifiable_type' => get_class($penerima),
                            'notifiable_id' => $penerima->id,
                            'data' => json_encode([
                                'format' => 'filament',
                                'title' => 'Pengajuan Pembatalan Pesanan',
                                'body' => "Pasien {$order->user->name} mengajukan pembatalan pesanan {$order->kode_order}. Mohon ditinjau.",
                                'color' => 'warning',
                                'icon' => 'heroicon-o-exclamation-triangle',
                                'iconColor' => 'warning',
                                'duration' => 'persistent',
                                'actions' => [],
                                'view' => 'filament-notifications::notification',
                                'viewData' => [],
                            ]),
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to insert cancellation notification: ' . $e->getMessage());
                    }
                }

                Log::info('Cancellation requested by patient', [
                    'order_id' => $order->order_id,
                    'user_id'  => $order->user_id,
                ]);
            });

            return back()->with('success', 'Permintaan pembatalan berhasil dikirim. Kami akan segera memprosesnya.');

        } catch (\Exception $e) {
            Log::error('Gagal request cancel order: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat mengajukan pembatalan.');
        }
    }
}
