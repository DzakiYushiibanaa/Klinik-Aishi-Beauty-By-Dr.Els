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
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id('rekam_medis_id');

            $table->string('no_kunjungan')->unique();

            $table->foreignId('appointment_id')
                    ->unique()
                    ->constrained('appointments', 'appointment_id')
                    ->cascadeOnDelete();
            $table->foreignId('pasien_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('dokter_id')->nullable()->constrained('users');
            $table->foreignId('terapis_id')->nullable()->constrained('users');

            $table->text('keluhan')->nullable();
            $table->text('diagnosa')->nullable();
            $table->text('tindakan')->nullable();

            $table->enum('status_tindakan', ['direncanakan','dilakukan','ditunda','dibatalkan'])
                ->default('direncanakan');

            $table->string('foto_sebelum_path')->nullable();
            $table->string('foto_sesudah_path')->nullable();

            $table->text('produk_rekomendasi')->nullable();
            $table->text('catatan_lanjutan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
