<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PatientForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberRegistrationController extends Controller
{
    /**
     * Menampilkan Form Pendaftaran Member
     */
    public function create()
    {
        $user = auth()->user();
        
        // Cek apakah user sudah member
        if ($user->pasienProfile && $user->pasienProfile->is_member) {
            return redirect()->route('dashboard')->with('info', 'Anda sudah terdaftar sebagai member.');
        }

        $pasienId = \App\Models\Pasien::where('user_id', $user->id)->value('pasien_id');

        // Cek apakah sudah ada pengajuan pending/menunggu bayar
        $pendingForm = PatientForm::where('pasien_id', $pasienId)
            ->where('tipe_form', 'registrasi_member')
            ->whereIn('status', ['baru', 'diproses']) // Sesuaikan dengan status flow Anda
            ->first();

        // Optional: Jika mau memblokir user buat form baru kalau yang lama belum selesai
        // if ($pendingForm) { ... }

        return view('member.register', compact('user'));
    }

    /**
     * Proses Simpan Form & Generate Order Membership
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // 1. SETUP VALIDASI
        // Kita tampung rules dalam variable array dulu biar bisa dimodifikasi
        $rules = [
            'golongan_darah'         => 'nullable|string',
            'riwayat_penyakit'       => 'nullable|string',
            'jenis_kulit'            => 'required|string',
            'keluhan_utama'          => 'required|string',
            'skincare_sebelumnya'    => 'nullable|string',
            'periode_skincare_bulan' => 'nullable|integer',
            'pernah_facial'          => 'nullable',
            'pernah_laser'           => 'nullable',
            'pernah_skin_booster'    => 'nullable',
            'pernah_inject_infus'    => 'nullable',
        ];

        // LOGIC TAMBAHAN: Jika No HP User kosong di database, wajib isi sekarang
        if (empty($user->phone)) {
            $rules['phone'] = 'required|numeric|digits_between:10,15';
        }

        // Jalankan validasi
        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // 2. UPDATE USER PROFILE (Jika HP baru diinput)
            if (empty($user->phone) && $request->filled('phone')) {
                $user->update([
                    'phone' => $request->phone
                ]);
            }

            $pasienId = \App\Models\Pasien::where('user_id', $user->id)->value('pasien_id');

            // 3. Simpan ke Patient Forms (Sebagai Data Pengajuan)
            $form = PatientForm::create([
                'pasien_id'         => $pasienId,
                'tipe_form'         => 'registrasi_member',
                'status'            => 'baru', // Status awal
                
                // Data Medis
                'golongan_darah'    => $validated['golongan_darah'] ?? null,
                'riwayat_penyakit'  => $validated['riwayat_penyakit'] ?? null,
                'jenis_kulit'       => $validated['jenis_kulit'],
                'keluhan_utama'     => $validated['keluhan_utama'],
                'skincare_sebelumnya' => $validated['skincare_sebelumnya'] ?? null,
                'periode_skincare_bulan' => $validated['periode_skincare_bulan'] ?? 0,
                
                // Checkbox (Konversi ke Boolean)
                'pernah_facial'       => $request->has('pernah_facial'),
                'pernah_laser'        => $request->has('pernah_laser'),
                'pernah_skin_booster' => $request->has('pernah_skin_booster'),
                'pernah_inject_infus' => $request->has('pernah_inject_infus'),
            ]);

            // 4. Buat Order Membership (Nominal Tetap 50rb)
            $membershipPrice = 50000; 
            $kodeOrder = $this->generateKodeOrder();

            $order = Order::create([
                'user_id'           => $user->id,
                'kode_order'        => $kodeOrder,
                'order_type'        => 'membership', // Tipe khusus membership
                'sumber'            => 'online',
                'dibuat_oleh_id'    => $user->id,
                'status'            => 'menunggu_pembayaran',
                
                // Kalkulasi Harga
                'subtotal'          => $membershipPrice,
                'diskon_total'      => 0,
                'ongkir'            => 0, // Digital product, no ongkir
                'grand_total'       => $membershipPrice,
                'total_weight_gram' => 0,

                // Info Pengiriman (Dummy)
                'shipping_method'   => 'digital', 
                'shipping_address'  => 'Digital Membership Activation',
                'shipping_status'   => 'selesai',
            ]);

            // 5. Buat Order Item (Detail Invoice)
            OrderItem::create([
                'order_id'      => $order->order_id,
                'item_type'     => 'membership',
                'description'   => 'Biaya Registrasi Member AishiBeauty',
                'qty'           => 1,
                'harga_satuan'  => $membershipPrice,
                'diskon'        => 0,
                'subtotal'      => $membershipPrice,
            ]);

            DB::commit();

            // 6. Redirect ke Halaman Pembayaran
            return redirect()->route('payment.show', $order->order_id)
                ->with('success', 'Pendaftaran member berhasil dikirim. Silakan selesaikan pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Member Registration Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memproses pendaftaran member. Silakan coba lagi.');
        }
    }

    /**
     * Helper Generate Kode Order (Sama seperti CheckoutController)
     * Sebaiknya ini di-refactor ke Service/Trait jika dipakai banyak tempat
     */
    /**
     * Helper Generate Kode Order Membership.
     * Format khusus member tetap dipertahankan: MEM-YYYYMMDD-XXXX (4 digit).
     */
    private function generateKodeOrder(): string
    {
        $date = now()->format('Ymd');
        $prefix = "MEM-{$date}-";

        $lastOrder = Order::where('kode_order', 'like', "{$prefix}%")
            ->orderBy('order_id', 'desc')
            ->first();

        $newNumber = $lastOrder ? (int) substr($lastOrder->kode_order, -4) + 1 : 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}