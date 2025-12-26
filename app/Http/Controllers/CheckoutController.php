<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Keranjang masih kosong.');
        }

        $subtotal = collect($cart)->sum(fn ($i) => (int)$i['harga'] * (int)$i['qty']);

        return view('checkout.index', compact('cart', 'subtotal'));
    }

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Keranjang masih kosong.');
        }

        $request->validate([
            'sumber' => ['required', 'in:online,offline'],
            'shipping_method' => ['nullable', 'string', 'max:255'],
            'shipping_address' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($request, $cart) {

            $userId = auth()->id();

            $subtotal = collect($cart)->sum(fn ($i) => (int)$i['harga'] * (int)$i['qty']);
            $diskonTotal = 0;
            $ongkir = 0;
            $grandTotal = max(0, ($subtotal - $diskonTotal) + $ongkir);

            $order = Order::create([
                'user_id' => $userId,
                'kode_order' => 'ORD-' . now()->format('Ymd') . '-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                'promotion_id' => null,
                'order_type' => 'produk',
                'sumber' => $request->input('sumber', 'online'),
                'dibuat_oleh_id' => $userId,

                'status' => 'menunggu_pembayaran', // nanti Midtrans update jadi lunas
                'payment_method' => null,
                'payment_channel' => null,
                'payment_ref' => null,

                'subtotal' => (int) $subtotal,
                'diskon_total' => (int) $diskonTotal,
                'ongkir' => (int) $ongkir,
                'grand_total' => (int) $grandTotal,
                'total_weight_gram' => 0,

                'shipping_method' => $request->input('shipping_method'),
                'shipping_address' => $request->input('shipping_address'),
                'shipping_status' => 'belum_dikirim',
            ]);

            foreach ($cart as $item) {
                $qty = (int) $item['qty'];
                $harga = (int) $item['harga'];
                $subtotalItem = $qty * $harga;

                OrderItem::create([
                    'order_id' => $order->order_id,
                    'appointment_id' => null,

                    'product_id' => $item['product_id'],
                    'treatment_id' => null,
                    'bundle_id' => null,

                    'item_type' => 'product',
                    'description' => $item['nama'],

                    'qty' => $qty,
                    'harga_satuan' => $harga,
                    'diskon' => 0,
                    'subtotal' => $subtotalItem,
                ]);
            }

            session()->forget('cart');

            return redirect()->route('landing')->with('success', 'Checkout berhasil. Silakan lanjut pembayaran (Midtrans menyusul).');
        });
    }
}
