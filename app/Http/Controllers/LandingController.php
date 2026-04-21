<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Treatment;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        // Ambil Produk Unggulan
        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->orderByDesc('product_id')
            ->limit(4) // Tampilkan 4 produk terbaru
            ->get();

        // Ambil Treatment Unggulan (Baru)
        $featuredTreatments = Treatment::query()
            ->where('is_active', true)
            ->orderByDesc('treatment_id')
            ->limit(4) // Tampilkan 4 treatment terbaru
            ->get();

        return view('welcome', [
            'featuredProducts' => $featuredProducts,
            'featuredTreatments' => $featuredTreatments, 
        ]);
    }
}