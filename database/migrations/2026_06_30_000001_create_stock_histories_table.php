<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id('stock_history_id');

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('order_id')->nullable();

            $table->enum('jenis', ['masuk', 'keluar']);
            $table->unsignedInteger('jumlah');

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('product_id')
                  ->references('product_id')->on('products')
                  ->cascadeOnDelete();

            $table->foreign('order_id')
                  ->references('order_id')->on('orders')
                  ->nullOnDelete();

            $table->index(['product_id', 'created_at']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
