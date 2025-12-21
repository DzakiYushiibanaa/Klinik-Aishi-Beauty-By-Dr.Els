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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('order_item_id');

            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();

            // kalau item berkaitan dengan booking
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->nullOnDelete();

            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('products')->nullOnDelete();

            $table->unsignedBigInteger('treatment_id')->nullable();
            $table->foreign('treatment_id')->references('treatment_id')->on('treatments')->nullOnDelete();

            $table->unsignedBigInteger('bundle_id')->nullable();
            $table->foreign('bundle_id')->references('bundle_id')->on('bundles')->nullOnDelete();

            $table->string('item_type'); // product/treatment/konsultasi/membership/bundle
            $table->string('description')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedInteger('harga_satuan')->default(0);
            $table->unsignedInteger('diskon')->default(0);
            $table->unsignedInteger('subtotal')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
