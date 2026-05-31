<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom `consultation_id` ke `order_items` agar order yang
 * berisi konsultasi bisa di-link langsung ke `consultations`.
 *
 * - Nullable supaya data lama tetap valid (item konsultasi lama hanya
 *   menyimpan nama di kolom `description`).
 * - On delete set null — kalau master konsultasi dihapus, riwayat
 *   transaksi tidak ikut hilang, deskripsi tetap menjadi snapshot.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'consultation_id')) {
                $table->unsignedBigInteger('consultation_id')
                    ->nullable()
                    ->after('bundle_id');

                $table->foreign('consultation_id')
                    ->references('consultation_id')
                    ->on('consultations')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'consultation_id')) {
                $table->dropForeign(['consultation_id']);
                $table->dropColumn('consultation_id');
            }
        });
    }
};
