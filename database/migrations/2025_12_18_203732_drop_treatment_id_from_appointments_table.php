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
            if (Schema::hasColumn('appointments', 'treatment_id')) {
                $table->dropForeign(['treatment_id']); // kalau ada FK
                $table->dropColumn('treatment_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('treatment_id')->nullable()->after('jenis');
            $table->foreign('treatment_id')->references('treatment_id')->on('treatments')->nullOnDelete();
        });
    }
};
