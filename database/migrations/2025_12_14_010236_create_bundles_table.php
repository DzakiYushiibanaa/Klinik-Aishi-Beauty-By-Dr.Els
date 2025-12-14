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
        Schema::create('bundles', function (Blueprint $table) {
            $table->id('bundle_id');
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('jenis'); // product, treatment, mixed
            $table->text('deskripsi')->nullable();
            $table->string('foto_path')->nullable();

            $table->integer('harga_normal')->default(0);
            $table->integer('harga_promo')->default(0);

            $table->boolean('is_member_only')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundles');
    }
};
