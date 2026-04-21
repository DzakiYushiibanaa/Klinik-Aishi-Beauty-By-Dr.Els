<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Order;
use App\Models\PatientForm;
use App\Models\User;
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

        $appointments = Appointment::where('user_id', $user->id)
            ->with(['dokter', 'terapis'])
            ->orderBy('tanggal_jam', 'desc')
            ->get();
            
        $pasienId = \App\Models\Pasien::where('user_id', $user->id)->value('pasien_id');

        $praKonsuls = PatientForm::where('pasien_id', $pasienId)
            ->with('dokter')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $orders = Order::where('user_id', $user->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard', compact('user', 'appointments', 'praKonsuls', 'orders'));
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
        
        return back()->with('success', 'Cache wilayah berhasil dihapus!');
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
            'foto'          => 'nullable|image|mimes:jpeg,jpg,png|max:2048', 
        ]);

        try {
            DB::transaction(function () use ($request, $user) {
                $user->update([
                    'name'          => $request->name,
                    'phone'         => $request->phone,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'tanggal_lahir' => $request->tanggal_lahir,
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
                    ]
                );
            });

            Log::info('Profile updated successfully', ['user_id' => $user->id]);
            return back()->with('success', 'Profil berhasil diperbarui! ✅');
            
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

        return back()->with('success', 'Terima kasih! Pesanan telah diselesaikan. 🎉');
    }

    public function cancelOrder(Order $order)
    {
        // 1. Validasi Pemilik
        if ($order->user_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Validasi Status
        if ($order->status !== 'menunggu_pembayaran') {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan.');
        }

        try {
            DB::transaction(function () use ($order) {
                
                // A. KEMBALIKAN STOK BARANG (RESTOCK) 📦
                // Kita load relasi items agar tidak query berulang
                foreach ($order->items as $item) {
                    
                    // KASUS 1: PRODUK BIASA
                    if ($item->item_type === 'product' && $item->product_id) {
                        $product = \App\Models\Product::find($item->product_id);
                        if ($product) {
                            $product->increment('stok', $item->qty);
                        }
                    } 
                    // KASUS 2: BUNDLE (FIXED)
                    elseif ($item->item_type === 'bundle' && $item->bundle_id) {
                        // Ambil Bundle beserta item-item penyusunnya
                        $bundle = \App\Models\Bundle::with('productItems.product')->find($item->bundle_id);
                        
                        if ($bundle) {
                            // Loop isi bundle, kembalikan stok produk aslinya
                            foreach ($bundle->productItems as $bItem) {
                                if ($bItem->product) {
                                    // Hitung: Qty Pesanan * Qty Item dalam Bundle
                                    $qtyToRestore = $item->qty * $bItem->qty;
                                    $bItem->product->increment('stok', $qtyToRestore);
                                }
                            }
                        }
                    }
                }

                // B. UPDATE STATUS JADI BATAL
                $order->update([
                    'status' => 'dibatalkan', // Sesuaikan enum di DB ('dibatalkan' atau 'batal')
                    'shipping_status' => 'dibatalkan'
                ]);

                // C. KIRIM NOTIFIKASI KE ADMIN & APOTEKER 🔔
                // Cari user dengan role: super_admin, admin, atau apoteker
                $penerimaNotif = \App\Models\User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['super_admin', 'admin', 'apoteker']);
                })->get();

                // Log untuk debugging
                Log::info('Sending cancellation notification', [
                    'order_id' => $order->order_id,
                    'recipients_count' => $penerimaNotif->count(),
                ]);

                // Kirim Notifikasi Database - Manual Insert
                foreach ($penerimaNotif as $penerima) {
                    try {
                        DB::table('notifications')->insert([
                            'id' => \Illuminate\Support\Str::uuid()->toString(),
                            'type' => 'Filament\\Notifications\\DatabaseNotification',
                            'notifiable_type' => get_class($penerima),
                            'notifiable_id' => $penerima->id,
                            'data' => json_encode([
                                'format' => 'filament',
                                'title' => 'Pesanan Dibatalkan User',
                                'body' => "Pasien {$order->user->name} membatalkan pesanan {$order->kode_order}. Stok barang sudah otomatis dikembalikan.",
                                'color' => 'danger',
                                'icon' => 'heroicon-o-x-circle',
                                'iconColor' => 'danger',
                                'duration' => 'persistent',
                                'actions' => [], // Kosongkan array actions biar aman json-nya
                                'view' => 'filament-notifications::notification', // Default view filament
                                'viewData' => [],
                            ]),
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to insert notification: ' . $e->getMessage());
                    }
                }

                Log::info('Order cancelled, stock restored & admin notified', ['order_id' => $order->order_id]);
            });

            return back()->with('success', 'Pesanan berhasil dibatalkan.');

        } catch (\Exception $e) {
            Log::error('Gagal cancel order: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat membatalkan pesanan.');
        }
    }
}