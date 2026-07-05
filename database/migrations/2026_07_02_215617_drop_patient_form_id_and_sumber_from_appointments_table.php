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
            if (Schema::hasColumn('appointments', 'patient_form_id')) {
                $table->dropForeign(['patient_form_id']);
                $table->dropColumn('patient_form_id');
            }
            if (Schema::hasColumn('appointments', 'sumber')) {
                $table->dropColumn('sumber');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'patient_form_id')) {
                $table->unsignedBigInteger('patient_form_id')->nullable();
                $table->foreign('patient_form_id')->references('patient_form_id')->on('patient_forms')->onDelete('set null');
            }
            if (!Schema::hasColumn('appointments', 'sumber')) {
                $table->string('sumber')->default('online');
            }
        });
    }
};
