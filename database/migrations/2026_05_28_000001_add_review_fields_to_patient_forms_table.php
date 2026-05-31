<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah field workflow review pra-konsultasi:
 *
 *   reviewed_by         : users.id dokter yang membuka panel review.
 *                         Bisa beda dengan dokter_id (dokter pemilik
 *                         appointment) — yang mengisi rekomendasi adalah
 *                         dokter yang menekan tombol "Mulai Review".
 *   review_started_at   : timestamp saat dokter mulai review (modal dibuka).
 *   reviewed_at         : timestamp saat rekomendasi dikirim.
 *   analisa_dokter      : analisa kondisi pasien (text panjang).
 *   catatan_tambahan    : catatan opsional dari dokter untuk pasien.
 *
 * Field rekomendasi item-item (treatment/produk/bundle) tetap pakai
 * tabel `pra_konsultasi_rekomendasi_items` yang sudah ada — tidak perlu
 * tabel baru.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_forms', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('dokter_id');
            }
            if (! Schema::hasColumn('patient_forms', 'review_started_at')) {
                $table->timestamp('review_started_at')->nullable()->after('reviewed_by');
            }
            if (! Schema::hasColumn('patient_forms', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('review_started_at');
            }
            if (! Schema::hasColumn('patient_forms', 'analisa_dokter')) {
                $table->text('analisa_dokter')->nullable()->after('rekomendasi_dokter');
            }
            if (! Schema::hasColumn('patient_forms', 'catatan_tambahan')) {
                $table->text('catatan_tambahan')->nullable()->after('analisa_dokter');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            foreach (['reviewed_by', 'review_started_at', 'reviewed_at', 'analisa_dokter', 'catatan_tambahan'] as $col) {
                if (Schema::hasColumn('patient_forms', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
