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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');

            $table->unsignedBigInteger('user_id'); // pasien (users.id)
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->string('kode_order')->unique(); // ORD-YYYYMMDD-0001

            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->foreign('promotion_id')->references('promotion_id')->on('promotions')->nullOnDelete();

            $table->string('order_type')->default('campuran'); // produk/treatment/konsultasi/membership/campuran
            $table->string('sumber')->default('online'); // online/offline
            $table->unsignedBigInteger('dibuat_oleh_id'); // users.id
            $table->foreign('dibuat_oleh_id')->references('id')->on('users')->cascadeOnDelete();

            $table->string('status')->default('draft'); // draft/menunggu_pembayaran/sudah_dibayar/batal
            $table->string('payment_method')->nullable(); // midtrans/cash
            $table->string('payment_channel')->nullable(); // qris/bca_va/dll
            $table->string('payment_ref')->nullable();
            $table->string('midtrans_order_id')->nullable();
            $table->string('snap_token')->nullable();

            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('diskon_total')->default(0);
            $table->unsignedInteger('ongkir')->default(0);
            $table->unsignedInteger('grand_total')->default(0);
            $table->unsignedInteger('total_weight_gram')->default(0);

            $table->string('shipping_method')->nullable(); // pickup/gosend/jnt
            $table->text('shipping_address')->nullable();
            $table->string('shipping_waybill')->nullable();
            $table->string('shipping_status')->default('belum_dikirim'); // belum_dikirim/dikirim/selesai/dibatalkan

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
