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
        Schema::table('dokter', function (Blueprint $table) {
            if (Schema::hasColumn('dokter', 'pengalaman_tahun')) {
                $table->dropColumn('pengalaman_tahun');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            if (!Schema::hasColumn('dokter', 'pengalaman_tahun')) {
                $table->unsignedSmallInteger('pengalaman_tahun')->nullable();
            }
        });
    }
};
