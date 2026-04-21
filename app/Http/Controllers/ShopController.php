<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory; // 🔥 Jangan lupa import ini
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil List Kategori untuk Dropdown
        // Asumsi: Nama tabel model ProductCategory punya kolom 'nama' atau 'name'. 
        // Sesuaikan jika error (misal: 'nama_kategori')
        $categories = ProductCategory::where('is_active', true)->orderBy('nama')->get();

        // 2. Query Produk dengan Filter
        $products = Product::query()
            ->where('is_active', true)
            // A. Logic Filter Kategori (Mendukung Array)
            ->when($request->filled('category'), function ($query) use ($request) {
            if (is_array($request->category)) {
                $query->whereIn('product_category_id', $request->category);
            }
            else if ($request->category !== 'all') {
                $query->where('product_category_id', $request->category);
            }
        })
            // B. Logic Search (Pencarian Nama)
            ->when($request->filled('search'), function ($query) use ($request) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        })
            // C. Logic Ketersediaan (Availability)
            ->when($request->filled('availability'), function ($query) use ($request) {
            if (is_array($request->availability)) {
                $query->where(function ($q) use ($request) {
                            if (in_array('in_stock', $request->availability)) {
                                $q->orWhere('stok', '>', 0);
                            }
                            if (in_array('out_of_stock', $request->availability)) {
                                $q->orWhere('stok', '=', 0);
                            }
                        }
                        );
                    }
                })
            ->orderBy('nama')
            ->paginate(12)
            ->withQueryString(); // 🔥 PENTING: Biar pas ganti halaman, filternya gak ilang

        // 3. Kirim variable $products DAN $categories ke view
        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        return view('shop.show', compact('product'));
    }
}