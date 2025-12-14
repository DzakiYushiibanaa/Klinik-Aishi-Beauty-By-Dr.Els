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
        Schema::create('bundle_treatment_items', function (Blueprint $table) {
            $table->id('bundle_treatment_item_id');
            $table->unsignedBigInteger('bundle_id');
            $table->unsignedBigInteger('treatment_id');
            $table->integer('qty')->default(1);

            $table->timestamps();

            $table->foreign('bundle_id')
                ->references('bundle_id')
                ->on('bundles')
                ->cascadeOnDelete();

            $table->foreign('treatment_id')
                ->references('treatment_id')
                ->on('treatments')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_treatment_items');
    }
};
