<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom handler & komisi per item layanan di tabel appointment_items.
 *
 *   - handled_by_type   : 'dokter' | 'terapis' (string sederhana)
 *   - handled_by_id     : users.id pelaku layanan
 *   - commission_percent: persentase komisi, default 10.00
 *   - commission_amount : nominal komisi yang sudah dihitung (Rupiah, integer)
 *
 * Catatan:
 *   - Tidak diberi FK ke users supaya appointment lama tetap aman bila
 *     handled_by_id null. Konsistensi ditegakkan via UI/Service layer.
 *   - Tidak ada tabel commission_records tambahan — komisi cukup
 *     disimpan di sini (sumber kebenaran tunggal).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointment_items', function (Blueprint $table) {
            $table->string('handled_by_type', 20)->nullable()->after('qty');
            $table->unsignedBigInteger('handled_by_id')->nullable()->after('handled_by_type');
            $table->decimal('commission_percent', 5, 2)->default(10.00)->after('handled_by_id');
            $table->unsignedBigInteger('commission_amount')->default(0)->after('commission_percent');

            $table->index(['handled_by_type', 'handled_by_id'], 'appointment_items_handler_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_items', function (Blueprint $table) {
            $table->dropIndex('appointment_items_handler_idx');
            $table->dropColumn([
                'handled_by_type',
                'handled_by_id',
                'commission_percent',
                'commission_amount',
            ]);
        });
    }
};
