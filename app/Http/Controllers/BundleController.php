<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function index(Request $request)    {
        $bundles = Bundle::query()
            ->where('is_active', true)
            ->with(['productItems.product', 'treatmentItems.treatment'])

            // Filter by jenis
            ->when($request->filled('jenis'), function ($q) use ($request) {
            if (is_array($request->jenis)) {
                $q->whereIn('jenis', $request->jenis);
            }
            else if ($request->jenis !== 'all') {
                $q->where('jenis', $request->jenis);
            }
        })

            // Search
            ->when($request->search, function ($q) use ($request) {
            $q->where('nama', 'like', '%' . $request->search . '%');
        })

            // Sorting
            ->when($request->sort, function ($q) use ($request) {
            switch ($request->sort) {
                case 'price_low':
                    $q->orderByRaw('COALESCE(harga_promo, harga_normal) ASC');
                    break;
                case 'price_high':
                    $q->orderByRaw('COALESCE(harga_promo, harga_normal) DESC');
                    break;
                case 'discount':
                    $q->orderByRaw('((harga_normal - COALESCE(harga_promo, harga_normal)) / harga_normal) DESC');
                    break;
                default: // latest
                    $q->latest();
            }
        }, function ($q) {
            $q->latest(); // default sorting
        })

            ->paginate(9);

        return view('bundle.index', compact('bundles'));    }

    public function show(Bundle $bundle)
    {
        // Pastikan bundle aktif, kalau tidak 404
        abort_unless($bundle->is_active, 404);

        // Load relasi agar di halaman detail bisa tampil isinya
        $bundle->load(['productItems.product', 'treatmentItems.treatment']);

        return view('bundle.show', compact('bundle'));
    }
}