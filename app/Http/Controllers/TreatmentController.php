<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\TreatmentCategory;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil List Kategori untuk Dropdown
        $categories = TreatmentCategory::where('is_active', true)->orderBy('nama')->get();

        // 2. Query Treatment dengan Filter
        $sort = $request->input('sort', 'price_low');

        $treatments = Treatment::query()
            ->where('is_active', true)

            ->when($request->filled('category'), function ($query) use ($request) {
                if (is_array($request->category)) {
                    $query->whereIn('treatment_category_id', $request->category);
                } elseif ($request->category !== 'all') {
                    $query->where('treatment_category_id', $request->category);
                }
            })

            ->when($request->search, function ($query) use ($request) {
                $query->where('nama', 'like', '%' . $request->search . '%');
            })

            ->when(true, function ($query) use ($sort) {
                match ($sort) {
                    'price_high' => $query->orderBy('harga', 'desc')->orderBy('nama'),
                    'newest'     => $query->orderByDesc('treatment_id'),
                    'name'       => $query->orderBy('nama'),
                    default      => $query->orderBy('harga', 'asc')->orderBy('nama'), // price_low
                };
            })
            ->paginate(12)
            ->withQueryString();

        return view('treatment.index', compact('treatments', 'categories', 'sort'));
    }

    public function show(Treatment $treatment)
    {
        abort_unless($treatment->is_active, 404);

        return view('treatment.show', compact('treatment'));
    }
}
