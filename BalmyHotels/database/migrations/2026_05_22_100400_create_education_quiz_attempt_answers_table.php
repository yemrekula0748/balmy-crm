<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_quiz_attempt_id')->constrained('education_quiz_attempts')->cascadeOnDelete();
            $table->foreignId('education_quiz_question_id')->constrained('education_quiz_questions')->cascadeOnDelete();
            $table->foreignId('education_quiz_option_id')->nullable()->constrained('education_quiz_options')->nullOnDelete();
            $table->boolean('is_correct')->default(false)->index();
            $table->timestamps();

            $table->unique(
                ['education_quiz_attempt_id', 'education_quiz_question_id'],
                'education_quiz_attempt_answer_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_quiz_attempt_answers');
    }
};
