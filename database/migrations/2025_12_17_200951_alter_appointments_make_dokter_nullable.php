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
        Schema::table('appointments', function (Blueprint $table) {
        $table->unsignedBigInteger('dokter_id')->nullable()->change();
        $table->unsignedBigInteger('terapis_id')->nullable()->change();   // biar konsultasi bisa null
        $table->unsignedBigInteger('treatment_id')->nullable()->change(); // biar konsultasi bisa null
        $table->unsignedBigInteger('patient_form_id')->nullable()->change(); // opsional
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
