<?php

namespace App\Support;

use App\Models\AppointmentItem;
use App\Models\Treatment;

/**
 * Helper tunggal untuk menghitung komisi treatment.
 *
 * Aturan:
 *   - Default komisi = 10% dari harga treatment (kolom `treatments.harga`).
 *   - Untuk item bertipe `bundle`, komisi = 10% dari harga bundle (promo
 *     kalau ada, fallback ke harga normal). Bundle satu paket = satu
 *     handler, anggap 1 unit komisi.
 *   - Untuk item bertipe non-treatment / non-bundle (mis. consultation),
 *     komisi 0 — fee konsultasi sudah dipegang langsung oleh dokter.
 *   - `qty` >= 1: komisi dikalikan qty supaya kalau klinik menjual
 *     paket multi-sesi, komisi staff tetap proporsional.
 *
 * Tetap simple — single static method. Tidak ada strategy pattern.
 */
class TreatmentCommissionCalculator
{
    /** Persentase komisi default (10%). */
    public const DEFAULT_PERCENT = 10.00;

    /**
     * Hitung nominal komisi (Rupiah, integer) untuk satu AppointmentItem.
     *
     * @param  AppointmentItem $item                    item tujuan
     * @param  float|null      $overridePercent         pakai persen ini kalau diberikan
     * @return int                                       nominal Rupiah, dibulatkan
     */
    public static function forItem(AppointmentItem $item, ?float $overridePercent = null): int
    {
        $percent = $overridePercent
            ?? (float) ($item->commission_percent ?: self::DEFAULT_PERCENT);

        $unitPrice = self::resolveUnitPrice($item);
        if ($unitPrice <= 0 || $percent <= 0) {
            return 0;
        }

        $qty = max(1, (int) ($item->qty ?? 1));

        return (int) round($unitPrice * $qty * ($percent / 100));
    }

    /**
     * Hitung komisi tanpa AppointmentItem (mis. saat preview di form):
     * pakai treatment_id + qty langsung.
     */
    public static function forTreatmentId(?int $treatmentId, int $qty = 1, float $percent = self::DEFAULT_PERCENT): int
    {
        if (!$treatmentId) {
            return 0;
        }

        $treatment = Treatment::find($treatmentId);
        if (!$treatment) {
            return 0;
        }

        $price = (int) ($treatment->harga ?? 0);
        if ($price <= 0 || $percent <= 0) {
            return 0;
        }

        $qty = max(1, $qty);
        return (int) round($price * $qty * ($percent / 100));
    }

    /**
     * Resolve harga satuan untuk item — selaras dengan logic di
     * StartServiceFinalizer::createOrder() agar nominal komisi
     * konsisten dengan total harga di nota.
     */
    private static function resolveUnitPrice(AppointmentItem $item): int
    {
        $type = strtolower((string) $item->item_type);

        if ($type === 'treatment') {
            return (int) ($item->treatment?->harga ?? 0);
        }

        if ($type === 'bundle' && $item->bundle) {
            return (int) ($item->bundle->harga_promo ?: $item->bundle->harga_normal);
        }

        // consultation, product, dsb. → tidak menghasilkan komisi treatment.
        return 0;
    }
}
