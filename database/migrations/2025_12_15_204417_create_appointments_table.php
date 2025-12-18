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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');

            $table->unsignedBigInteger('user_id'); // pasien
            $table->unsignedBigInteger('dokter_id'); // dokter
            $table->unsignedBigInteger('terapis_id')->nullable(); // terapis (opsional)
            $table->unsignedBigInteger('patient_form_id')->nullable(); // sumber pra-konsul (opsional)

            $table->string('sumber')->default('online'); // online, offline
            $table->unsignedBigInteger('dibuat_oleh_id'); // pasien/admin

            $table->string('jenis'); // konsultasi, treatment
            $table->unsignedBigInteger('treatment_id')->nullable(); // wajib kalau jenis=treatment

            $table->dateTime('tanggal_jam');
            $table->string('status')->default('diajukan'); // diajukan, disetujui, batal, selesai, tidak_hadir

            $table->text('catatan_pasien')->nullable();
            $table->text('catatan_klinik')->nullable();

            $table->string('canceled_by')->nullable(); // pasien/klinik/null
            $table->text('cancel_reason')->nullable();

            $table->timestamps();

            // FK
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('dokter_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('terapis_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('dibuat_oleh_id')->references('id')->on('users')->restrictOnDelete();

            // sesuaikan kalau patient_forms & treatments PK kamu bukan default
            $table->foreign('patient_form_id')->references('patient_form_id')->on('patient_forms')->nullOnDelete();
            $table->foreign('treatment_id')->references('treatment_id')->on('treatments')->nullOnDelete();

            $table->index(['user_id', 'tanggal_jam']);
            $table->index(['dokter_id', 'tanggal_jam']);
            $table->index(['terapis_id', 'tanggal_jam']);
            $table->index(['status']);
            $table->index(['jenis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
