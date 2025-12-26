<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->paginate(12);

        return view('shop.index', compact('products'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        return view('shop.show', compact('product'));
    }
}
