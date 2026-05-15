<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Bundle;
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
             return back()->with('info', 'Produk sudah ada di keranjang Anda.');
        }

        $cart[$key] = [
            'type'       => 'product',
            'id'         => $product->product_id,
            'product_id' => $product->product_id, 
            'bundle_id'  => null, 
            'nama'       => $product->nama,
            'harga'      => (int) $product->harga,
            'berat_gram' => (int) $product->berat_gram,
            'foto_path'  => $product->foto_path,
            'qty'        => $qty,
        ];

        $this->putCart($cart);
        return redirect()->route('cart.index')->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    // ==========================================
    // 2. ADD BUNDLE (FIXED - Kalkulasi Berat Real)
    // ==========================================
    public function addBundle(Request $request, Bundle $bundle)
    {
        abort_unless($bundle->is_active, 404);

        $qty  = max(1, (int) $request->input('qty', 1));
        $cart = $this->cart();
        $key  = 'bundle_' . $bundle->bundle_id;

        if (isset($cart[$key])) {
            return back()->with('info', 'Paket sudah ada di keranjang Anda.');
        }

        // 🔥 HITUNG BERAT REAL DARI ISI BUNDLE
        $totalBerat = 0;
        
        // Load relasi untuk hitung berat
        $bundle->load(['productItems.product', 'treatmentItems.treatment']);
        
        foreach ($bundle->productItems as $item) {
            if ($item->product) {
                $totalBerat += (int)$item->product->berat_gram * (int)$item->qty;
            }
        }
        
        // Treatment gak ada berat fisik, tapi kalau mau set default per treatment (optional)
        // Misal: 1 treatment = 0 gram (karena jasa)
        
        // Minimal 100 gram sebagai fallback
        $totalBerat = max($totalBerat, 100);

        $harga = $bundle->harga_promo > 0 ? $bundle->harga_promo : $bundle->harga_normal;

        $cart[$key] = [
            'type'       => 'bundle',
            'id'         => $bundle->bundle_id,
            'product_id' => null, 
            'bundle_id'  => $bundle->bundle_id,
            'nama'       => $bundle->nama,
            'harga'      => (int) $harga,
            'berat_gram' => $totalBerat, // 🔥 Berat calculated
            'foto_path'  => $bundle->foto_path,
            'qty'        => $qty,
        ];

        $this->putCart($cart);
        return back()->with('success', 'Paket hemat berhasil ditambahkan ke keranjang.');
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