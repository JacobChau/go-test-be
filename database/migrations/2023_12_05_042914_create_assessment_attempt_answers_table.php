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
        Schema::create('assessment_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->text('answer_content');
            $table->foreignId('assessment_attempt_id')->constrained('assessment_attempts');
            $table->foreignId('assessment_question_id')->constrained('assessment_questions');
            $table->foreignId('question_option_id')->constrained('question_options');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_attempt_answers');
    }
};
