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
        Schema::create('dokter', function (Blueprint $table) {
            $table->id('dokter_id');
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('kode_dokter')->unique();
            $table->string('spesialis');
            $table->unsignedSmallInteger('pengalaman_tahun')->nullable();
            $table->string('status_praktik')->default('aktif'); // aktif/nonaktif
            $table->string('foto_profil_path')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokter');
    }
};
