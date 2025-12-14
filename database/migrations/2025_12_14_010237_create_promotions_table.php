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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id('promotion_id');
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();

            $table->string('jenis'); // order, treatment, bundle, dll.
            $table->integer('min_subtotal')->default(0);
            $table->integer('diskon_nominal')->nullable();
            $table->integer('diskon_persen')->nullable();

            $table->date('start_date');
            $table->date('end_date');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
