<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\TreatmentCategory; // 🔥 Jangan lupa import ini!
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil List Kategori untuk Dropdown
        // Asumsi: Model TreatmentCategory punya kolom 'nama'. 
        // Kalau error kolom tidak ditemukan, cek database apakah namanya 'nama' atau 'name'.
        $categories = TreatmentCategory::where('is_active', true)->orderBy('nama')->get();

        // 2. Query Treatment dengan Filter
        $treatments = Treatment::query()
            ->where('is_active', true)

            // A. Logic Filter Kategori
            ->when($request->filled('category'), function ($query) use ($request) {
            if (is_array($request->category)) {
                $query->whereIn('treatment_category_id', $request->category);
            }
            else if ($request->category !== 'all') {
                $query->where('treatment_category_id', $request->category);
            }
        })

            // B. Logic Search (Cari Nama Treatment)
            ->when($request->search, function ($query) use ($request) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        })

            ->orderBy('nama', 'asc')
            ->paginate(12)
            ->withQueryString(); // 🔥 Biar filter gak ilang pas ganti halaman

        return view('treatment.index', compact('treatments', 'categories'));
    }

    public function show(Treatment $treatment)
    {
        abort_unless($treatment->is_active, 404);

        return view('treatment.show', compact('treatment'));
    }
}