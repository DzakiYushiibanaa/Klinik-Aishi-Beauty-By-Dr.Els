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
        Schema::create('jadwal_praktiks', function (Blueprint $table) {
            $table->id('jadwal_praktik_id');
            $table->unsignedBigInteger('user_id'); // dokter/terapis

            $table->string('jenis_layanan'); // konsultasi, treatment
            $table->string('hari'); // senin, selasa, dst.
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('durasi_slot_menit')->default(60);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id') // <- kalau users PK lu "user_id", ubah jadi "user_id"
                ->on('users')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_praktiks');
    }
};
