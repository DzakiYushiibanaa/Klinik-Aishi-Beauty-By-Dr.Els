<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rapikan skema patient_forms:
 *
 *   - Drop kolom `analisa_dokter` (duplikatif dengan kolom diagnosa baru).
 *   - Rename `rekomendasi_dokter` → `diagnosa` (semantik lebih tepat:
 *     dokter mengisi diagnosa kondisi pasien, sedangkan rekomendasi
 *     treatment/produk sudah ditampung di tabel
 *     `pra_konsultasi_rekomendasi_items`).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            if (Schema::hasColumn('patient_forms', 'analisa_dokter')) {
                $table->dropColumn('analisa_dokter');
            }
        });

        Schema::table('patient_forms', function (Blueprint $table) {
            if (Schema::hasColumn('patient_forms', 'rekomendasi_dokter')
                && ! Schema::hasColumn('patient_forms', 'diagnosa')
            ) {
                $table->renameColumn('rekomendasi_dokter', 'diagnosa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            if (Schema::hasColumn('patient_forms', 'diagnosa')
                && ! Schema::hasColumn('patient_forms', 'rekomendasi_dokter')
            ) {
                $table->renameColumn('diagnosa', 'rekomendasi_dokter');
            }
        });

        Schema::table('patient_forms', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_forms', 'analisa_dokter')) {
                $table->text('analisa_dokter')->nullable()->after('rekomendasi_dokter');
            }
        });
    }
};
