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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id('consultation_id');
            $table->string('nama');
            $table->integer('harga');
            $table->integer('durasi_menit')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default data
        DB::table('consultations')->insert([
            [
                'nama' => 'Konsultasi Awal',
                'harga' => 125000,
                'durasi_menit' => 60,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Konsultasi Lanjutan',
                'harga' => 100000,
                'durasi_menit' => 60,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
