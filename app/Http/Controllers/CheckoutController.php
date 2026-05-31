<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Bundle;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Keranjang Anda kosong. Silakan tambahkan produk terlebih dahulu.');
        }
        
        $subtotal = collect($cart)->sum(fn ($i) => (int)$i['harga'] * (int)$i['qty']);
        $pasien = auth()->user()->pasienProfile ?? null;

        // Kalau profil belum lengkap, simpan URL ini supaya setelah profil
        // disimpan user bisa langsung balik ke checkout dengan cart utuh.
        $userPhone = auth()->user()->phone ?? null;
        $missingProfile = !$pasien
            || empty($userPhone)
            || empty($pasien->district_id)
            || empty($pasien->alamat);

        if ($missingProfile) {
            session(['profile_required_redirect' => url()->current()]);
        }

        return view('checkout.index', compact('cart', 'subtotal', 'pasien'));
    }

    public function checkOngkir(Request $request)
    {
        try {
            $destinationDistrictId = $request->input('district_id');
            
            if (!$destinationDistrictId) {
                return response()->json(['error' => 'District ID required'], 400);
            }

            $cart = session()->get('cart', []);
            $totalWeight = 0;
            $hasInvalidWeight = false;
            
            foreach ($cart as $item) {
                $beratGram = (int)($item['berat_gram'] ?? 0);
                
                if ($beratGram <= 0) {
                    $hasInvalidWeight = true;
                    Log::warning('Item without weight', [
                        'type' => $item['type'] ?? 'unknown',
                        'id' => $item['id'] ?? null,
                        'nama' => $item['nama'] ?? 'unknown'
                    ]);
                    $beratGram = 100;
                }
                
                $totalWeight += $beratGram * (int)$item['qty'];
            }
            
            if ($hasInvalidWeight) {
                Log::warning('Some items have invalid weight, using fallback 100g per item');
            }

            $weight = max($totalWeight, 1);
            
            Log::info('Weight calculation', [
                'total_weight_gram' => $totalWeight,
                'weight_sent_to_api' => $weight
            ]);

            $originDistrictId = config('rajaongkir.origin_district_id');
            
            if (!$originDistrictId) {
                Log::error('Origin district not configured');
                return response()->json(['error' => 'Origin district not configured'], 500);
            }

            $couriers = config('rajaongkir.couriers', ['jne', 'jnt', 'pos']);
            $results = [];
            $failedCouriers = [];

            foreach ($couriers as $code) {
                try {
                    $response = Http::timeout(10)->asForm()->withHeaders([
                        'Accept' => 'application/json',
                        'key'    => config('rajaongkir.api_key'),
                    ])->post('https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost', [
                        'origin'      => $originDistrictId,
                        'destination' => $destinationDistrictId,
                        'weight'      => $weight,
                        'courier'     => $code
                    ]);

                    Log::info("RajaOngkir API Request - {$code}", [
                        'endpoint' => 'calculate/district/domestic-cost',
                        'origin' => $originDistrictId,
                        'destination' => $destinationDistrictId,
                        'weight' => $weight,
                        'response_status' => $response->status()
                    ]);

                    if ($response->successful()) {
                        $fullResponse = $response->json();
                        $dataArray = $fullResponse['data'] ?? [];
                        
                        if (!empty($dataArray)) {
                            foreach ($dataArray as $service) {
                                if (isset($service['service'], $service['cost'], $service['etd'])) {
                                    $results[] = [
                                        'courier_code' => strtoupper($code),
                                        'service'      => $service['service'],
                                        'cost'         => (int) $service['cost'],
                                        'etd'          => $service['etd'],
                                        'weight_gram'  => $weight
                                    ];
                                }
                            }
                            Log::info("{$code} shipping loaded", [
                                'count' => count($dataArray),
                                'weight' => $weight
                            ]);
                        }
                    } else {
                        $failedCouriers[] = $code;
                        Log::warning("Failed ongkir {$code}", [
                            'status' => $response->status(),
                            'body' => $response->body()
                        ]);
                    }
                } catch (\Exception $e) {
                    $failedCouriers[] = $code;
                    Log::error("Exception {$code}: " . $e->getMessage());
                }
            }

            if (empty($results)) {
                return response()->json([
                    'error' => 'Tidak ada layanan kurir tersedia',
                    'failed_couriers' => $failedCouriers
                ], 404);
            }

            return response()->json($results);
            
        } catch (\Exception $e) {
            Log::error('Error calculating shipping: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to calculate shipping'], 500);
        }
    }

    public function checkPromo(Request $request)
    {
        $request->validate([
            'kode' => 'required|string',
            'subtotal' => 'required|numeric'
        ]);

        $code = $request->kode;
        $subtotal = (int) $request->subtotal;
        $today = Carbon::now();

        $promo = Promotion::where('kode', $code)->first();

        if (!$promo) {
            return response()->json(['valid' => false, 'message' => 'Kode promo tidak ditemukan.'], 404);
        }

        if (!$promo->is_active || $today->lt($promo->start_date) || $today->gt($promo->end_date)) {
            return response()->json(['valid' => false, 'message' => 'Kode promo sudah kedaluwarsa atau tidak aktif.'], 400);
        }

        if ($subtotal < $promo->min_subtotal) {
            return response()->json([
                'valid' => false, 
                'message' => 'Minimal belanja Rp ' . number_format($promo->min_subtotal, 0, ',', '.') . ' untuk menggunakan kode ini.'
            ], 400);
        }

        $discountAmount = 0;
        if ($promo->diskon_nominal) {
            $discountAmount = $promo->diskon_nominal;
        } elseif ($promo->diskon_persen) {
            $discountAmount = ($subtotal * $promo->diskon_persen) / 100;
        }

        $discountAmount = min($discountAmount, $subtotal);

        return response()->json([
            'valid' => true,
            'kode' => $promo->kode,
            'discount_amount' => $discountAmount,
            'message' => 'Kode promo berhasil digunakan!'
        ]);
    }

    // ==========================================
    // 🔥 STORE - FIXED: SUPPORT BUNDLE
    // ==========================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'district_id'      => 'nullable|string',
            'shipping_cost'    => 'required|numeric|min:0',
            'shipping_courier' => 'required|string',
            'shipping_notes'   => 'nullable|string|max:500',
            'promo_code'       => 'nullable|string',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong. Silakan tambahkan produk terlebih dahulu.');
        }

        $pasien = auth()->user()->pasienProfile;
        $userPhone = auth()->user()->phone ?? null;

        $missingProfile = !$pasien
            || empty($userPhone)
            || empty($pasien->district_id)
            || empty($pasien->alamat);

        if ($missingProfile) {
            // Simpan URL keranjang supaya user balik ke cart setelah lengkapi profil.
            // Cart session TIDAK disentuh — produk tetap aman.
            session(['profile_required_redirect' => route('cart.index')]);

            return redirect()
                ->route('dashboard', ['tab' => 'profile'])
                ->with('error', 'Lengkapi nomor HP dan alamat pengiriman di profil Anda terlebih dahulu sebelum melanjutkan pembayaran.')
                ->with('active_tab', 'profile');
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $totalWeight = 0;

            // 1. CEK STOK & HITUNG (Dengan Safety Check ??)
            foreach ($cart as $item) {
                // Ambil type dengan aman
                $type = $item['type'] ?? 'product'; 
                $qty  = (int)($item['qty'] ?? 1);
                $harga = (int)($item['harga'] ?? 0);
                $berat = (int)($item['berat_gram'] ?? 0);
                if ($berat <= 0) $berat = 100; // Default berat

                if ($type === 'product') {
                    // Ambil ID dengan aman
                    $prodId = $item['product_id'] ?? $item['id']; 
                    $product = Product::find($prodId);
                    
                    if (!$product || $product->stok < $qty) {
                        throw new \Exception("Stok produk " . ($item['nama'] ?? 'Unknown') . " habis/kurang.");
                    }
                } 
                // Kalau Bundle, kita skip cek stok produk utama (asumsi selalu ready atau cek komponen jika perlu)

                $subtotal += $harga * $qty;
                $totalWeight += $berat * $qty;
            }

            // 2. Diskon Logic (Sama)
            $diskonTotal = 0;
            if (!empty($validated['promo_code'])) {
                $promo = Promotion::where('kode', $validated['promo_code'])->first();
                // Simplifikasi logic promo
                if ($promo && $subtotal >= $promo->min_subtotal) {
                     if ($promo->diskon_nominal) $diskonTotal = $promo->diskon_nominal;
                     elseif ($promo->diskon_persen) $diskonTotal = ($subtotal * $promo->diskon_persen) / 100;
                }
            }

            // 3. Create Order
            $ongkir = (int)$validated['shipping_cost'];
            $grandTotal = ($subtotal + $ongkir) - $diskonTotal;
            if ($grandTotal < 0) $grandTotal = 0;

            $fullAddress = trim(implode(', ', array_filter([
                $pasien->alamat, $pasien->district_name, $pasien->kota, $pasien->provinsi, $pasien->kode_pos
            ])));

            $order = Order::create([
                'user_id'           => auth()->id(),
                'kode_order'        => $this->generateKodeOrder(),
                'order_type'        => 'produk',
                'sumber'            => 'online',
                'dibuat_oleh_id'    => auth()->id(),
                'status'            => 'menunggu_pembayaran',
                'subtotal'          => $subtotal,
                'diskon_total'      => $diskonTotal,
                'ongkir'            => $ongkir,
                'grand_total'       => $grandTotal,
                'total_weight_gram' => $totalWeight,
                'shipping_method'   => $validated['shipping_courier'],
                'shipping_address'  => $fullAddress,
                'shipping_notes'    => $validated['shipping_notes'] ?? null,
                'shipping_status'   => 'belum_dikirim',
                'expired_at'        => now()->addHours(24),
            ]);

            // 4. Simpan Item (Safety Check lagi)
            $stockDeducted = false;

            foreach ($cart as $item) {
                $type = $item['type'] ?? 'product'; // Default product biar aman
                $qty  = (int)($item['qty'] ?? 1);
                
                // Tentukan ID
                $productId = null;
                $bundleId  = null;

                if ($type === 'product') {
                    $productId = $item['product_id'] ?? $item['id'];
                } elseif ($type === 'bundle') {
                    $bundleId = $item['bundle_id'] ?? $item['id'];
                }

                OrderItem::create([
                    'order_id'       => $order->order_id,
                    'product_id'     => $productId,
                    'bundle_id'      => $bundleId,
                    'item_type'      => $type,
                    'description'    => $item['nama'] ?? 'Item Tanpa Nama',
                    'qty'            => $qty,
                    'harga_satuan'   => (int)($item['harga'] ?? 0),
                    'diskon'         => 0,
                    'subtotal'       => (int)($item['harga'] ?? 0) * $qty,
                ]);

                // Kurangi Stok Produk
                if ($type === 'product' && $productId) {
                    $prod = Product::find($productId);
                    if ($prod) {
                        $prod->decrement('stok', $qty);
                        $stockDeducted = true;
                    }
                }
                
                // Kurangi Stok Bundle (Opsional: Jika stok bundle diambil dari produk item)
                if ($type === 'bundle' && $bundleId) {
                    $bundle = Bundle::with('productItems.product')->find($bundleId);
                    if ($bundle) {
                        foreach ($bundle->productItems as $bItem) {
                            if($bItem->product) {
                                $bItem->product->decrement('stok', $bItem->qty * $qty);
                                $stockDeducted = true;
                            }
                        }
                    }
                }
            }

            if ($stockDeducted) {
                $order->update(['is_stock_deducted' => true]);
            }

            DB::commit();
            session()->forget('cart'); // HAPUS CART SETELAH SUKSES

            return redirect()->route('payment.show', $order->order_id)
                ->with('success', 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses pesanan. Silakan coba lagi.');
        }
    }

    public function payment($id)
    {
        $order = Order::with('items')->findOrFail($id);

        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        if ($order->status !== 'menunggu_pembayaran') {
            return redirect()->route('dashboard')
                ->with('info', 'Pesanan ini sudah diproses atau dibayar.');
        }

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $params = [
            'transaction_details' => [
                'order_id' => $order->kode_order,
                'gross_amount' => (int) $order->grand_total,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => auth()->user()->phone ?? '',
            ],
            'item_details' => $order->items->map(function($item) {
                return [
                    'id' => $item->product_id ?? 'BUNDLE-' . $item->bundle_id ?? 'ITEM-' . $item->order_item_id,
                    'price' => (int) $item->harga_satuan,
                    'quantity' => (int) $item->qty,
                    'name' => substr($item->description, 0, 50)
                ];
            })->push([
                'id' => 'SHIPPING',
                'price' => (int) $order->ongkir,
                'quantity' => 1,
                'name' => 'Ongkos Kirim - ' . $order->shipping_method
            ])->toArray()
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            Log::info('Snap token generated', [
                'order_id' => $order->order_id,
                'kode_order' => $order->kode_order
            ]);

            return view('payment.show', compact('order', 'snapToken'));

        } catch (\Exception $e) {
            Log::error('Midtrans Snap error', [
                'order_id' => $order->order_id,
                'message' => $e->getMessage()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Gagal memproses pembayaran. Silakan coba lagi.');
        }
    }

    private function generateKodeOrder(): string
    {
        return \App\Support\OrderCodeGenerator::next();
    }
}
