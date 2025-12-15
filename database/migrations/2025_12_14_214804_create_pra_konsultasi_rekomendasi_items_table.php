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
        Schema::create('pra_konsultasi_rekomendasi_items', function (Blueprint $table) {
            $table->id('pra_konsultasi_rekomendasi_item_id');

            $table->unsignedBigInteger('patient_form_id');

            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('treatment_id')->nullable();
            $table->unsignedBigInteger('bundle_id')->nullable();

            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('patient_form_id')
                ->references('patient_form_id')
                ->on('patient_forms')
                ->cascadeOnDelete();

            $table->foreign('product_id')->references('product_id')->on('products')->nullOnDelete();
            $table->foreign('treatment_id')->references('treatment_id')->on('treatments')->nullOnDelete();
            $table->foreign('bundle_id')->references('bundle_id')->on('bundles')->nullOnDelete();

            $table->index(['patient_form_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pra_konsultasi_rekomendasi_items');
    }
};
