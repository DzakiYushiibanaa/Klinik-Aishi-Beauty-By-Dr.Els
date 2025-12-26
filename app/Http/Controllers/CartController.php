<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function cart(): array
    {
        return session()->get('cart', []);
    }

    private function putCart(array $cart): void
    {
        session()->put('cart', $cart);
    }

    public function index()
    {
        $cart = $this->cart();

        $subtotal = collect($cart)->sum(
            fn ($i) => (int) $i['harga'] * (int) $i['qty']
        );

        return view('cart.index', compact('cart', 'subtotal'));
    }

    public function add(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        $qty  = max(1, (int) $request->input('qty', 1));
        $cart = $this->cart();

        $key = (string) $product->product_id;

        // ⛔ PRODUK SUDAH ADA → JANGAN TAMBAH LAGI
        if (isset($cart[$key])) {
            return back()->with('info', 'Produk sudah ada di keranjang.');
        }

        // ✅ PRODUK BARU
        $cart[$key] = [
            'product_id' => $product->product_id,
            'nama'       => $product->nama,
            'harga'      => (int) $product->harga,
            'foto_path'  => $product->foto_path,
            'qty'        => $qty,
        ];

        $this->putCart($cart);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request)
    {
        $items = $request->input('items', []);
        $cart  = $this->cart();

        foreach ($items as $productId => $qty) {
            if (!isset($cart[$productId])) continue;

            $qty = (int) $qty;

            if ($qty <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['qty'] = min(99, $qty);
            }
        }

        $this->putCart($cart);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function remove(Product $product)
    {
        $cart = $this->cart();
        unset($cart[(string) $product->product_id]);

        $this->putCart($cart);

        return back()->with('success', 'Item dihapus.');
    }
}
