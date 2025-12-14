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
        Schema::create('treatments', function (Blueprint $table) {
            $table->id('treatment_id');
            $table->unsignedBigInteger('treatment_category_id');

            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->integer('harga');
            $table->integer('durasi_menit')->default(0);
            $table->string('foto_path')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('treatment_category_id')
                ->references('treatment_category_id')
                ->on('treatment_categories')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
