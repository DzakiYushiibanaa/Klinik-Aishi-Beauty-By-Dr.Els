<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migrasi normalisasi status pembayaran:
 *   'lunas' / 'paid'  →  'sudah_dibayar'
 *
 * Setelah migrasi ini, hanya nilai 'sudah_dibayar' yang dipakai untuk
 * menandakan order yang sudah dibayar. Nilai 'lunas' di-deprecate.
 */
return new class extends Migration
{
    public function up(): void
    {
        $affected = DB::table('orders')
            ->whereIn('status', ['lunas', 'paid'])
            ->update(['status' => 'sudah_dibayar']);

        // Log untuk traceability (akan muncul di artisan output)
        if ($affected > 0) {
            echo "  - Normalized {$affected} orders.status from 'lunas'/'paid' → 'sudah_dibayar'\n";
        }
    }

    public function down(): void
    {
        // Tidak rollback — nilai 'lunas' sudah deprecated.
        // Jika perlu pulihkan, lakukan manual.
    }
};
