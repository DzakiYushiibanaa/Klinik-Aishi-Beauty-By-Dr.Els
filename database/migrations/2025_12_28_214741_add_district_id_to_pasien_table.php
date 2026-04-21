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
        Schema::table('pasien', function (Blueprint $table) {
            // Tambah kolom untuk ID wilayah Raja Ongkir
            $table->string('province_id')->nullable()->after('provinsi');
            $table->string('city_id')->nullable()->after('province_id');
            $table->string('district_id')->nullable()->after('city_id');
            $table->string('district_name')->nullable()->after('district_id');
            
            // Index untuk performance query
            $table->index('district_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->dropIndex(['district_id']);
            $table->dropColumn(['province_id', 'city_id', 'district_id', 'district_name']);
        });
    }
};
