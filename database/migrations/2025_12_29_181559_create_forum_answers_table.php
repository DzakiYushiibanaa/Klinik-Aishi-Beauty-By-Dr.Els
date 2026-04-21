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
        Schema::create('forum_answers', function (Blueprint $table) {
            $table->id('forum_answer_id');
            $table->foreignId('question_id')->constrained('forum_questions', 'forum_question_id')->cascadeOnDelete();
            $table->foreignId('dokter_id')->constrained('users')->cascadeOnDelete();

            $table->text('body');

            $table->timestamps();

            // Index untuk performance
            $table->index('question_id');
            $table->index('dokter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_answers');
    }
};
