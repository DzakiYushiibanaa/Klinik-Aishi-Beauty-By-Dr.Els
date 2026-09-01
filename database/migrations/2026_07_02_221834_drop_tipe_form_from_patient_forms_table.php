<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            if (Schema::hasColumn('patient_forms', 'tipe_form')) {
                // $table->dropColumn('tipe_form');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_forms', 'tipe_form')) {
                $table->string('tipe_form')->default('pra-konsultasi');
            }
        });
    }
};
