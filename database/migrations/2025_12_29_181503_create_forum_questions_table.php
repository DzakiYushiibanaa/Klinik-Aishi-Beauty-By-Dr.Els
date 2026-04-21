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
        Schema::create('forum_questions', function (Blueprint $table) {
            $table->id('forum_question_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->text('body');
            $table->boolean('is_anonymous')->default(false);
            $table->string('status')->default('dibuka'); // dibuka / ditutup

            $table->timestamps();

            // Index untuk performance
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_questions');
    }
};
