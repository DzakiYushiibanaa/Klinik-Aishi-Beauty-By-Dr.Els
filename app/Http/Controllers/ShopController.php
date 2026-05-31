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
        $categories = ProductCategory::where('is_active', true)->orderBy('nama')->get();

        // 2. Query Produk dengan Filter
        $sort = $request->input('sort', 'price_low');

        $products = Product::query()
            ->where('is_active', true)
            ->when($request->filled('category'), function ($query) use ($request) {
                if (is_array($request->category)) {
                    $query->whereIn('product_category_id', $request->category);
                } elseif ($request->category !== 'all') {
                    $query->where('product_category_id', $request->category);
                }
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('nama', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('availability'), function ($query) use ($request) {
                if (is_array($request->availability)) {
                    $query->where(function ($q) use ($request) {
                        if (in_array('in_stock', $request->availability)) {
                            $q->orWhere('stok', '>', 0);
                        }
                        if (in_array('out_of_stock', $request->availability)) {
                            $q->orWhere('stok', '=', 0);
                        }
                    });
                }
            })
            ->when(true, function ($query) use ($sort) {
                match ($sort) {
                    'price_high' => $query->orderBy('harga', 'desc')->orderBy('nama'),
                    'newest'     => $query->orderByDesc('product_id'),
                    'name'       => $query->orderBy('nama'),
                    default      => $query->orderBy('harga', 'asc')->orderBy('nama'), // price_low
                };
            })
            ->paginate(12)
            ->withQueryString();

        return view('shop.index', compact('products', 'categories', 'sort'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        return view('shop.show', compact('product'));
    }
}