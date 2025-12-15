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
        Schema::create('patient_forms', function (Blueprint $table) {
            $table->id('patient_form_id');

            $table->unsignedBigInteger('user_id');   // pasien
            $table->unsignedBigInteger('dokter_id')->nullable(); // dokter review

            $table->string('tipe_form'); // pra_konsul, registrasi_member, offline_intake

            $table->string('golongan_darah')->nullable();
            $table->text('riwayat_penyakit')->nullable();

            $table->string('jenis_kulit')->nullable();
            $table->text('skincare_sebelumnya')->nullable();
            $table->integer('periode_skincare_bulan')->nullable();

            $table->boolean('pernah_facial')->default(false);
            $table->boolean('pernah_skin_booster')->default(false);
            $table->boolean('pernah_laser')->default(false);
            $table->boolean('pernah_inject_infus')->default(false);

            $table->text('keluhan_utama')->nullable();

            $table->string('foto_depan_path')->nullable();
            $table->string('foto_kiri_path')->nullable();
            $table->string('foto_kanan_path')->nullable();

            $table->string('status')->default('baru'); // baru, diproses, direkomendasikan, ditutup
            $table->text('rekomendasi_dokter')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('dokter_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['user_id', 'status']);
            $table->index(['dokter_id', 'status']);
            $table->index(['tipe_form']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_forms');
    }
};
