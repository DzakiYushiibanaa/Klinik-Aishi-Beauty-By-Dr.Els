<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->orderByDesc('product_id')
            ->limit(6)
            ->get();

        return view('welcome', [
            'featuredProducts' => $featuredProducts,
        ]);
    }
}
