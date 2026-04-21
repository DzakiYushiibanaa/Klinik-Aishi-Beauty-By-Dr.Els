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
            $table->foreign('pasien_id')->references('pasien_id')->on('pasien')->restrictOnDelete();
            $table->index(['pasien_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            $table->dropForeign(['pasien_id']);
            $table->dropIndex(['pasien_id', 'status']);
        });
    }
};
