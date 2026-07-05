<?php

namespace App\Http\Controllers;

use App\Models\PatientForm;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PraKonsulController extends Controller
{
    /** Field foto yang ditangani. */
    private const PHOTO_FIELDS = ['foto_depan', 'foto_kiri', 'foto_kanan'];

    /** Aturan validasi tiap foto (dipakai ulang). */
    private const PHOTO_RULES = [
        'image',
        'mimes:jpg,jpeg,png,webp',
        'max:4096',
        'dimensions:min_width=720,min_height=720',
    ];

    public function create()
    {
        $tempPhotos = $this->tempPhotos();
        $tempUrls = [];
        foreach (self::PHOTO_FIELDS as $field) {
            $tempUrls[$field] = isset($tempPhotos[$field]) && Storage::disk('public')->exists($tempPhotos[$field])
                ? Storage::disk('public')->url($tempPhotos[$field])
                : null;
        }

        return view('pra.create', [
            'tempPhotoUrls' => $tempUrls,
            'tempPhotoNames' => array_combine(
                self::PHOTO_FIELDS,
                array_map(
                    fn ($f) => isset($tempPhotos[$f]) ? 'Foto sebelumnya tersimpan' : '',
                    self::PHOTO_FIELDS
                )
            ),
        ]);
    }

    public function store(Request $request)
    {
        // ─────────────────────────────────────────────
        // STEP 1. Simpan foto valid yg diupload kali ini ke folder temp.
        //         Foto-foto valid tetap kepakai meskipun field lain gagal.
        // ─────────────────────────────────────────────
        $tempPhotos = $this->tempPhotos();
        foreach (self::PHOTO_FIELDS as $field) {
            if ($request->hasFile($field) && $request->file($field)->isValid()) {
                $singleValidator = Validator::make(
                    [$field => $request->file($field)],
                    [$field => self::PHOTO_RULES]
                );
                if ($singleValidator->passes()) {
                    // Hapus temp lama untuk field ini (kalau ada).
                    if (isset($tempPhotos[$field]) && Storage::disk('public')->exists($tempPhotos[$field])) {
                        Storage::disk('public')->delete($tempPhotos[$field]);
                    }
                    $tempPhotos[$field] = $request->file($field)->store(
                        $this->tempDir(),
                        'public'
                    );
                }
            }
        }
        $this->saveTempPhotos($tempPhotos);

        // ─────────────────────────────────────────────
        // STEP 2. Validasi keseluruhan. Foto yg sudah punya temp → tidak required.
        // ─────────────────────────────────────────────
        $rules = [
            'golongan_darah'         => ['nullable', 'in:A,B,AB,O,-'],
            'riwayat_penyakit'       => ['nullable', 'string'],
            'jenis_kulit'            => ['nullable', 'in:Kering,Berminyak,Kombinasi,Sensitif,Normal'],
            'skincare_sebelumnya'    => ['nullable', 'string', 'max:2000'],
            'periode_skincare_bulan' => ['nullable', 'integer', 'min:0', 'max:240'],

            'pernah_facial'       => ['nullable'],
            'pernah_skin_booster' => ['nullable'],
            'pernah_laser'        => ['nullable'],
            'pernah_inject_infus' => ['nullable'],

            'keluhan_utama' => ['required', 'string', 'min:10'],
        ];

        foreach (self::PHOTO_FIELDS as $field) {
            $hasTemp = isset($tempPhotos[$field]) && Storage::disk('public')->exists($tempPhotos[$field]);
            $rules[$field] = array_merge(
                [$hasTemp ? 'nullable' : 'required'],
                self::PHOTO_RULES
            );
        }

        $messages = [
            'keluhan_utama.required' => 'Keluhan utama wajib diisi.',
            'keluhan_utama.min'      => 'Keluhan utama minimal 10 karakter.',

            'foto_depan.required'   => 'Foto wajah depan wajib diunggah.',
            'foto_depan.image'      => 'File harus berupa gambar.',
            'foto_depan.mimes'      => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
            'foto_depan.max'        => 'Ukuran foto maksimal 4MB.',
            'foto_depan.dimensions' => 'Resolusi foto minimal 720x720 pixel.',

            'foto_kiri.required'    => 'Foto wajah kiri wajib diunggah.',
            'foto_kiri.image'       => 'File harus berupa gambar.',
            'foto_kiri.mimes'       => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
            'foto_kiri.max'         => 'Ukuran foto maksimal 4MB.',
            'foto_kiri.dimensions'  => 'Resolusi foto minimal 720x720 pixel.',

            'foto_kanan.required'   => 'Foto wajah kanan wajib diunggah.',
            'foto_kanan.image'      => 'File harus berupa gambar.',
            'foto_kanan.mimes'      => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
            'foto_kanan.max'        => 'Ukuran foto maksimal 4MB.',
            'foto_kanan.dimensions' => 'Resolusi foto minimal 720x720 pixel.',

            'jenis_kulit.in'    => 'Pilihan jenis kulit tidak valid.',
            'golongan_darah.in' => 'Pilihan golongan darah tidak valid.',
        ];

        $data = $request->validate($rules, $messages);

        // ─────────────────────────────────────────────
        // STEP 3. Simpan ke DB (final). Foto: prefer upload baru,
        //         kalau tidak ada pakai file temp.
        // ─────────────────────────────────────────────
        $form = new PatientForm();
        $form->pasien_id = \App\Models\Pasien::where('user_id', auth()->id())->value('pasien_id');
        $form->dokter_id = null;


        $form->golongan_darah         = $data['golongan_darah'] ?? null;
        $form->riwayat_penyakit       = $data['riwayat_penyakit'] ?? null;
        $form->jenis_kulit            = $data['jenis_kulit'] ?? null;
        $form->skincare_sebelumnya    = $data['skincare_sebelumnya'] ?? null;
        $form->periode_skincare_bulan = $data['periode_skincare_bulan'] ?? null;

        $form->pernah_facial       = $request->boolean('pernah_facial');
        $form->pernah_skin_booster = $request->boolean('pernah_skin_booster');
        $form->pernah_laser        = $request->boolean('pernah_laser');
        $form->pernah_inject_infus = $request->boolean('pernah_inject_infus');

        $form->keluhan_utama = $data['keluhan_utama'];
        $form->status        = 'baru';

        foreach (self::PHOTO_FIELDS as $field) {
            $columnName = $field . '_path';
            if ($request->hasFile($field)) {
                $form->{$columnName} = $request->file($field)->store('pra-konsul', 'public');
                // Hapus temp untuk field ini supaya tidak boros disk.
                if (isset($tempPhotos[$field]) && Storage::disk('public')->exists($tempPhotos[$field])) {
                    Storage::disk('public')->delete($tempPhotos[$field]);
                }
            } elseif (isset($tempPhotos[$field]) && Storage::disk('public')->exists($tempPhotos[$field])) {
                $tempPath = $tempPhotos[$field];
                $ext = pathinfo($tempPath, PATHINFO_EXTENSION) ?: 'jpg';
                $finalPath = 'pra-konsul/' . Str::random(40) . '.' . $ext;
                Storage::disk('public')->move($tempPath, $finalPath);
                $form->{$columnName} = $finalPath;
            }
        }

        $form->save();

        // ─────────────────────────────────────────────
        // STEP 4. Cleanup session + folder temp user.
        // ─────────────────────────────────────────────
        $this->clearTempPhotos();

        // 🔔 WhatsApp ke pasien (best-effort).
        try {
            $formForWa = PatientForm::with(['pasien.user', 'dokter'])
                ->find($form->getKey());
            if ($formForWa) {
                app(FonnteService::class)->sendPraKonsulSubmitted($formForWa);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Fonnte WA send gagal (pra-konsul submitted): '.$e->getMessage());
        }

        return redirect()->route('dashboard', ['tab' => 'prakonsul'])
            ->with('success', 'Pra-konsultasi berhasil dikirim.');
    }

    // ───── Helper privat ────────────────────────────

    private function tempSessionKey(): string
    {
        return 'pra_konsul_temp_photos_' . auth()->id();
    }

    private function tempDir(): string
    {
        return 'pra-konsul-tmp/' . auth()->id();
    }

    /** @return array<string, string> */
    private function tempPhotos(): array
    {
        $stored = session($this->tempSessionKey(), []);
        // Bersihkan key yg file-nya sudah hilang (mis. dihapus manual / disk reset).
        return collect($stored)
            ->filter(fn ($p) => is_string($p) && Storage::disk('public')->exists($p))
            ->all();
    }

    private function saveTempPhotos(array $tempPhotos): void
    {
        session([$this->tempSessionKey() => $tempPhotos]);
    }

    private function clearTempPhotos(): void
    {
        session()->forget($this->tempSessionKey());
        Storage::disk('public')->deleteDirectory($this->tempDir());
    }
}
