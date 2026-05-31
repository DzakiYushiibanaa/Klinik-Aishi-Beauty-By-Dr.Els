<?php

namespace App\Services\PraKonsul;

use App\Models\PatientForm;
use App\Models\PraKonsultasiRekomendasiItem;
use Illuminate\Support\Facades\DB;

/**
 * Service tipis untuk menangani siklus review pra-konsultasi:
 *
 *   1. start()  — saat dokter klik "Mulai Review".
 *                 Set status = ditinjau, reviewed_by = dokter,
 *                 review_started_at = now() (jika belum di-set).
 *
 *   2. submit() — saat dokter klik "Kirim Rekomendasi".
 *                 Persist analisa, catatan, replace seluruh rekomendasi
 *                 items (treatment + produk), lalu set status =
 *                 direkomendasikan + reviewed_at = now().
 *
 * Observer `PatientFormObserver` yang sudah ada akan otomatis kirim
 * notifikasi WhatsApp setelah status berubah ke direkomendasikan
 * — kita tidak perlu kirim ulang di sini.
 */
class ReviewSubmissionService
{
    /**
     * Tandai form sebagai "Sedang Direview" oleh dokter aktif.
     * Idempotent: aman dipanggil ulang.
     */
    public function start(PatientForm $form, int $reviewerUserId): PatientForm
    {
        $payload = [
            'reviewed_by' => $reviewerUserId,
        ];

        // Hanya update status & timestamp pada transisi pertama agar
        // dokter bisa menutup-buka panel tanpa menggeser timestamp awal.
        if ($form->status !== 'ditinjau') {
            $payload['status'] = 'ditinjau';
        }
        if (! $form->review_started_at) {
            $payload['review_started_at'] = now();
        }

        $form->update($payload);

        return $form->refresh();
    }

    /**
     * Simpan hasil review akhir & ubah status ke "direkomendasikan".
     *
     * @param  PatientForm $form
     * @param  array       $data    raw form data dari Filament action
     * @return PatientForm
     */
    public function submit(PatientForm $form, array $data): PatientForm
    {
        return DB::transaction(function () use ($form, $data) {
            // 1. Replace seluruh rekomendasi item TERLEBIH DAHULU.
            //    Urutan ini penting: observer PatientForm yang akan
            //    memicu kirim WhatsApp baru jalan saat status berubah
            //    ke "direkomendasikan" (langkah 2 di bawah). Kalau item
            //    belum di-insert saat itu, pesan WA jadi tampilkan
            //    "Tidak ada rekomendasi" walaupun dokter sudah memilih.
            $form->rekomendasiItems()->delete();

            foreach ((array) ($data['treatment_recommendations'] ?? []) as $row) {
                $type = $row['item_type'] ?? 'treatment';
                $treatmentId = $type === 'treatment' ? (int) ($row['treatment_id'] ?? 0) : null;
                $bundleId    = $type === 'bundle'    ? (int) ($row['bundle_id'] ?? 0)    : null;

                if (! $treatmentId && ! $bundleId) continue;

                PraKonsultasiRekomendasiItem::create([
                    'patient_form_id' => $form->patient_form_id,
                    'treatment_id'    => $treatmentId ?: null,
                    'bundle_id'       => $bundleId ?: null,
                    'product_id'      => null,
                    'catatan'         => $row['catatan'] ?? null,
                ]);
            }

            foreach ((array) ($data['product_recommendations'] ?? []) as $row) {
                $productId = (int) ($row['product_id'] ?? 0);
                if (! $productId) continue;

                PraKonsultasiRekomendasiItem::create([
                    'patient_form_id' => $form->patient_form_id,
                    'product_id'      => $productId,
                    'treatment_id'    => null,
                    'bundle_id'       => null,
                    'catatan'         => $row['catatan'] ?? null,
                ]);
            }

            // 2. Update field utama + status. Pastikan reviewed_by
            //    selalu terisi (fallback ke user aktif) supaya kolom
            //    "Reviewer" di tabel admin tidak strip.
            $reviewerId = $form->reviewed_by ?: \Filament\Facades\Filament::auth()->id() ?: auth()->id();

            $form->update([
                'diagnosa'          => $data['diagnosa'] ?? null,
                'catatan_tambahan'  => $data['catatan_tambahan'] ?? null,
                'status'            => 'direkomendasikan',
                'reviewed_at'       => now(),
                'review_started_at' => $form->review_started_at ?: now(),
                'reviewed_by'       => $reviewerId,
            ]);

            return $form->refresh();
        });
    }
}
