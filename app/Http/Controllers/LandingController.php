<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Product;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LandingController extends Controller
{
    public function index()
    {
        // ─── Produk Unggulan ──────────────────────────────
        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->orderByDesc('product_id')
            ->limit(4)
            ->get();

        // ─── Treatment Unggulan ───────────────────────────
        $featuredTreatments = Treatment::query()
            ->where('is_active', true)
            ->orderByDesc('treatment_id')
            ->limit(4)
            ->get();

        // ─── Stats Hero ──────────────────────────────────
        $stats = $this->buildStats();

        return view('landing', [
            'featuredProducts'   => $featuredProducts,
            'featuredTreatments' => $featuredTreatments,
            'stats'              => $stats,
        ]);
    }

    /**
     * Statistik real-time untuk hero stats:
     * - pasien_terlayani: jumlah pasien unik yang punya appointment selesai.
     * - jenis_perawatan: jumlah treatment aktif.
     * - ahli_kecantikan: dokter (status praktik aktif) + terapis aktif.
     */
    private function buildStats(): array
    {
        $pasienTerlayani = (int) Appointment::query()
            ->where('status', 'selesai')
            ->distinct('user_id')
            ->count('user_id');

        $jenisPerawatan = (int) Treatment::query()
            ->where('is_active', true)
            ->count();

        $dokterAktif = (int) User::query()
            ->role('dokter')
            ->whereHas('dokterProfile', function (Builder $q) {
                $q->whereIn('status_praktik', ['aktif', 'Aktif']);
            })
            ->count();

        $terapisAktif = (int) User::query()
            ->role('terapis')
            ->whereHas('terapisProfile')
            ->count();

        return [
            'pasien_terlayani' => $pasienTerlayani,
            'jenis_perawatan'  => $jenisPerawatan,
            'ahli_kecantikan'  => $dokterAktif + $terapisAktif,
        ];
    }
}
