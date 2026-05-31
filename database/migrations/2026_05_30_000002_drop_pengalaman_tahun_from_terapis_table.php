<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus kolom `pengalaman_tahun` pada tabel `terapis`.
 *
 * Kolom ini sudah tidak ditampilkan di UI maupun laporan terapis,
 * jadi disimpan tidak menambah informasi yang berguna lagi.
 *
 * Migration idempotent — cek `hasColumn` lebih dulu — dan punya
 * `down()` untuk membalik kalau perlu rollback.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('terapis', 'pengalaman_tahun')) {
            Schema::table('terapis', function (Blueprint $table) {
                $table->dropColumn('pengalaman_tahun');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('terapis', 'pengalaman_tahun')) {
            Schema::table('terapis', function (Blueprint $table) {
                $table->unsignedSmallInteger('pengalaman_tahun')
                    ->nullable()
                    ->after('kode_terapis');
            });
        }
    }
};
