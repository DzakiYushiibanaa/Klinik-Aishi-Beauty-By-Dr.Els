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
        Schema::create('appointment_items', function (Blueprint $table) {
            $table->id('appointment_item_id');

            // FK -> appointments.appointment_id (karena PK appointments bukan id)
            $table->unsignedBigInteger('appointment_id');
            $table->foreign('appointment_id')
                ->references('appointment_id')
                ->on('appointments')
                ->cascadeOnDelete();

            $table->enum('item_type', ['treatment', 'bundle']);

            // treatment
            $table->unsignedBigInteger('treatment_id')->nullable();
            $table->foreign('treatment_id')
                ->references('treatment_id')
                ->on('treatments')
                ->nullOnDelete();

            // bundle
            $table->unsignedBigInteger('bundle_id')->nullable();
            $table->foreign('bundle_id')
                ->references('bundle_id')
                ->on('bundles')
                ->nullOnDelete();

            $table->unsignedInteger('qty')->default(1);

            $table->timestamps();

            // optional index biar query cepat
            $table->index(['appointment_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_items');
    }
};
