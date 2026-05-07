<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah payment_expired_at ke appointments
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('payment_expired_at')->nullable()->after('tanggal_jam');
        });

        // Tambah expired_at ke orders
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('expired_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('payment_expired_at');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('expired_at');
        });
    }
};
