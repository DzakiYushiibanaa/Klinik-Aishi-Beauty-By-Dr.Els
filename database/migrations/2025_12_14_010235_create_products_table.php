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
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
        $table->unsignedBigInteger('product_category_id');

        $table->string('nama');
        $table->text('deskripsi')->nullable();
        $table->integer('harga');
        $table->integer('berat_gram')->default(0);
        $table->integer('stok')->default(0);
        $table->string('foto_path')->nullable();
        $table->boolean('is_active')->default(true);

        $table->timestamps();

        $table->foreign('product_category_id')
            ->references('product_category_id')
            ->on('product_categories')
            ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
