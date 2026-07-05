<?php

namespace App\Observers;

use App\Models\PatientForm;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;

/**
 * Observer untuk PatientForm.
 *
 * Tujuan: kirim WhatsApp ke pasien saat dokter selesai meninjau form
 * pra-konsultasi (status berubah ke `direkomendasikan` atau `selesai`).
 *
 * Aturan:
 *   - Hanya untuk tipe `pra_konsul` (skip `registrasi_member` dll).
 *   - Hanya kirim sekali per transisi: status SEBELUMNYA bukan
 *     `direkomendasikan`/`selesai` dan SEKARANG menjadi salah satunya.
 *   - Best-effort: failure di-log warning, tidak throw.
 */
class PatientFormObserver
{
    /** Status yang menandai dokter sudah memberi rekomendasi. */
    private const REVIEWED_STATUSES = ['direkomendasikan', 'selesai'];

    public function updated(PatientForm $form): void
    {


        // Pastikan status memang berubah pada save ini.
        if (!$form->wasChanged('status')) {
            return;
        }

        $newStatus = strtolower((string) $form->status);
        if (!in_array($newStatus, self::REVIEWED_STATUSES, true)) {
            return;
        }

        // Hindari pengiriman ulang kalau status sebelumnya juga sudah reviewed.
        $prevStatus = strtolower((string) $form->getOriginal('status'));
        if (in_array($prevStatus, self::REVIEWED_STATUSES, true)) {
            return;
        }

        try {
            $form->loadMissing([
                'pasien.user',
                'dokter',
                'reviewer',
                'rekomendasiItems.treatment',
                'rekomendasiItems.product',

            ]);
            app(FonnteService::class)->sendPraKonsulReviewed($form);
        } catch (\Throwable $e) {
            Log::warning('Fonnte WA send gagal (pra-konsul reviewed): '.$e->getMessage());
        }
    }
}
