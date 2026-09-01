<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            $table->text('riwayat_alergi')->nullable()->after('riwayat_penyakit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            $table->dropColumn('riwayat_alergi');
        });
    }
};
