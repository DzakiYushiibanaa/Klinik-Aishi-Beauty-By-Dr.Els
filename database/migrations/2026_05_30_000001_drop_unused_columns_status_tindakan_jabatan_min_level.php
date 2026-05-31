<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus kolom-kolom yang sudah tidak relevan lagi:
 *
 *   - rekam_medis.status_tindakan
 *     Rekam medis hanya muncul untuk layanan yang sudah selesai,
 *     status tindakan tidak menambah informasi.
 *
 *   - terapis.jabatan
 *     Aturan handler treatment kini ditentukan oleh
 *     `treatment_categories.{doctor_allowed,therapist_allowed}`,
 *     bukan oleh jabatan terapis.
 *
 *   - treatments.min_level
 *     Sama: rule handler ada di kategori, level terapis tidak dipakai
 *     lagi untuk pembatasan booking.
 *
 * Migration ini idempotent (cek `hasColumn` lebih dulu) dan punya
 * `down()` untuk membalik kalau perlu rollback.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('rekam_medis', 'status_tindakan')) {
            Schema::table('rekam_medis', function (Blueprint $table) {
                $table->dropColumn('status_tindakan');
            });
        }

        if (Schema::hasColumn('terapis', 'jabatan')) {
            Schema::table('terapis', function (Blueprint $table) {
                $table->dropColumn('jabatan');
            });
        }

        if (Schema::hasColumn('treatments', 'min_level')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->dropColumn('min_level');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('rekam_medis', 'status_tindakan')) {
            Schema::table('rekam_medis', function (Blueprint $table) {
                $table->enum('status_tindakan', ['direncanakan', 'dilakukan', 'ditunda', 'dibatalkan'])
                    ->default('dilakukan')
                    ->after('tindakan');
            });
        }

        if (! Schema::hasColumn('terapis', 'jabatan')) {
            Schema::table('terapis', function (Blueprint $table) {
                $table->string('jabatan')->nullable()->after('kode_terapis');
            });
        }

        if (! Schema::hasColumn('treatments', 'min_level')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->unsignedTinyInteger('min_level')->default(1)->after('is_active');
            });
        }
    }
};
