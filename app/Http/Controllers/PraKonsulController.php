<?php

namespace App\Http\Controllers;

use App\Models\PatientForm;
use Illuminate\Http\Request;

class PraKonsulController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $pasienId = \App\Models\Pasien::where('user_id', $userId)->value('pasien_id');

        $forms = PatientForm::query()
            ->where('pasien_id', $pasienId)
            ->latest('created_at')
            ->paginate(10);

        // ✅ map urutan form berdasarkan tanggal (paling lama = #1)
        $idsAsc = PatientForm::query()
            ->where('pasien_id', $pasienId)
            ->orderBy('created_at')
            ->pluck('patient_form_id')
            ->values()
            ->toArray();

        $formNumberMap = [];
        foreach ($idsAsc as $i => $id) {
            $formNumberMap[$id] = $i + 1;
        }

        return view('pra.index', compact('forms', 'formNumberMap'));
    }

    public function show(PatientForm $form)
    {
        $pasienId = \App\Models\Pasien::where('user_id', auth()->id())->value('pasien_id');

        // ✅ pastikan cuma pemilik yang bisa lihat
        abort_unless($form->pasien_id === $pasienId, 403);

        // ✅ hitung nomor urutan form ini (berdasarkan created_at paling awal)
        $nomor = PatientForm::query()
            ->where('pasien_id', $pasienId)
            ->where('created_at', '<=', $form->created_at)
            ->count();

        // ✅ rekomendasi (pakai relasi pra_konsultasi_rekomendasi_items)
        // Pastikan model PatientForm punya relasi: rekomendasiItems()
        $form->load([
            'dokter',
            'rekomendasiItems.product',
            'rekomendasiItems.treatment',
            'rekomendasiItems.bundle',
        ]);

        // grouping biar gampang tampilin
        $rekomProduk = $form->rekomendasiItems->filter(fn ($it) => $it->product_id && $it->product);
        $rekomTreatment = $form->rekomendasiItems->filter(fn ($it) => $it->treatment_id && $it->treatment);
        $rekomBundle = $form->rekomendasiItems->filter(fn ($it) => $it->bundle_id && $it->bundle);

        return view('pra.show', compact('form', 'nomor', 'rekomProduk', 'rekomTreatment', 'rekomBundle'));
    }

    public function create()
    {
        return view('pra.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'golongan_darah' => ['nullable', 'in:A,B,AB,O,-'],
            'riwayat_penyakit' => ['nullable','string'],
            'jenis_kulit' => ['nullable', 'in:Kering,Berminyak,Kombinasi,Sensitif,Normal'],
            'skincare_sebelumnya' => ['nullable', 'string', 'max:2000'],
            'periode_skincare_bulan' => ['nullable', 'integer', 'min:0', 'max:240'],

            'pernah_facial' => ['nullable'],
            'pernah_skin_booster' => ['nullable'],
            'pernah_laser' => ['nullable'],
            'pernah_inject_infus' => ['nullable'],

            'keluhan_utama' => ['required','string','min:10'],

            'foto_depan' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=720,min_height=720'],
            'foto_kiri' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=720,min_height=720'],
            'foto_kanan' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=720,min_height=720'],
        ], [
            'keluhan_utama.required' => 'Keluhan utama wajib diisi.',
            'keluhan_utama.min' => 'Keluhan utama minimal 10 karakter.',
            'foto_depan.required' => 'Foto wajah depan wajib diunggah.',
            'foto_depan.image' => 'File harus berupa gambar.',
            'foto_depan.mimes' => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
            'foto_depan.max' => 'Ukuran foto maksimal 4MB.',
            'foto_depan.dimensions' => 'Resolusi foto minimal 720x720 pixel.',
            'foto_kiri.required' => 'Foto wajah kiri wajib diunggah.',
            'foto_kiri.image' => 'File harus berupa gambar.',
            'foto_kiri.mimes' => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
            'foto_kiri.max' => 'Ukuran foto maksimal 4MB.',
            'foto_kiri.dimensions' => 'Resolusi foto minimal 720x720 pixel.',
            'foto_kanan.required' => 'Foto wajah kanan wajib diunggah.',
            'foto_kanan.image' => 'File harus berupa gambar.',
            'foto_kanan.mimes' => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
            'foto_kanan.max' => 'Ukuran foto maksimal 4MB.',
            'foto_kanan.dimensions' => 'Resolusi foto minimal 720x720 pixel.',
            'jenis_kulit.in' => 'Pilihan jenis kulit tidak valid.',
            'golongan_darah.in' => 'Pilihan golongan darah tidak valid.',
        ]);

        $form = new PatientForm();
        $form->pasien_id = \App\Models\Pasien::where('user_id', auth()->id())->value('pasien_id');
        $form->dokter_id = null;
        $form->tipe_form = 'pra_konsul';

        $form->golongan_darah = $data['golongan_darah'] ?? null;
        $form->riwayat_penyakit = $data['riwayat_penyakit'] ?? null;
        $form->jenis_kulit = $data['jenis_kulit'] ?? null;
        $form->skincare_sebelumnya = $data['skincare_sebelumnya'] ?? null;
        $form->periode_skincare_bulan = $data['periode_skincare_bulan'] ?? null;

        $form->pernah_facial = $request->boolean('pernah_facial');
        $form->pernah_skin_booster = $request->boolean('pernah_skin_booster');
        $form->pernah_laser = $request->boolean('pernah_laser');
        $form->pernah_inject_infus = $request->boolean('pernah_inject_infus');

        $form->keluhan_utama = $data['keluhan_utama'];
        $form->status = 'baru';

        // upload foto (public disk)
        if ($request->hasFile('foto_depan')) {
            $form->foto_depan_path = $request->file('foto_depan')->store('pra-konsul', 'public');
        }
        if ($request->hasFile('foto_kiri')) {
            $form->foto_kiri_path = $request->file('foto_kiri')->store('pra-konsul', 'public');
        }
        if ($request->hasFile('foto_kanan')) {
            $form->foto_kanan_path = $request->file('foto_kanan')->store('pra-konsul', 'public');
        }

        $form->save();

        return redirect()->route('pra.index')->with('success', 'Pra-konsultasi berhasil dikirim.');
    }
}
