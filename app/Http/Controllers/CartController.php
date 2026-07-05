<?php

namespace App\Http\Controllers;

use App\Models\Product;

use Illuminate\Http\Request;

class CartController extends Controller
{
    // Helper ambil cart
    private function cart(): array
    {
        return session()->get('cart', []);
    }

    // Helper simpan cart
    private function putCart(array $cart): void
    {
        session()->put('cart', $cart);
    }

    public function index()
    {
        $cart = $this->cart();
        $subtotal = collect($cart)->sum(fn ($i) => (int)$i['harga'] * (int)$i['qty']);
        return view('cart.index', compact('cart', 'subtotal'));
    }

    // ==========================================
    // 1. ADD PRODUCT
    // ==========================================
    public function add(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        $qty  = max(1, (int) $request->input('qty', 1));
        $cart = $this->cart();
        $key  = 'product_' . $product->product_id; // 🔥 Prefix agar konsisten

        if (isset($cart[$key])) {
            // Update qty kalau dipanggil ulang dengan jumlah berbeda (misal dari halaman detail).
            $cart[$key]['qty'] = min(99, max((int) $cart[$key]['qty'], $qty));
            $this->putCart($cart);

            $message = 'Jumlah produk di keranjang Anda diperbarui.';
        } else {
            $cart[$key] = [
                'type'       => 'product',
                'id'         => $product->product_id,
                'product_id' => $product->product_id,

                'nama'       => $product->nama,
                'harga'      => (int) $product->harga,
                'berat_gram' => (int) $product->berat_gram,
                'foto_path'  => $product->foto_path,
                'qty'        => $qty,
            ];
            $this->putCart($cart);

            $message = 'Produk berhasil ditambahkan ke keranjang.';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => $message,
                'name'       => $product->nama,
                'cart_count' => collect($this->cart())->sum('qty'),
            ]);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }


    // ==========================================
    // 3. UPDATE
    // ==========================================
    public function update(Request $request)
    {
        $items = $request->input('items', []);
        $cart  = $this->cart();

        foreach ($items as $key => $qty) {
            if (!isset($cart[$key])) continue;
            $qty = (int) $qty;
            if ($qty <= 0) {
                unset($cart[$key]);
            } else {
                $cart[$key]['qty'] = min(99, $qty);
            }
        }
        $this->putCart($cart);
        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    // ==========================================
    // 4. REMOVE
    // ==========================================
    public function remove($id)
    {
        $cart = $this->cart();
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $this->putCart($cart);
        }
        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }
}
