<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('education_quiz_attempt_answers');

        Schema::create('education_quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_quiz_attempt_id');
            $table->foreignId('education_quiz_question_id');
            $table->foreignId('education_quiz_option_id')->nullable();
            $table->boolean('is_correct')->default(false)->index();
            $table->timestamps();

            $table->foreign('education_quiz_attempt_id', 'edu_qaa_attempt_fk')
                ->references('id')
                ->on('education_quiz_attempts')
                ->cascadeOnDelete();

            $table->foreign('education_quiz_question_id', 'edu_qaa_question_fk')
                ->references('id')
                ->on('education_quiz_questions')
                ->cascadeOnDelete();

            $table->foreign('education_quiz_option_id', 'edu_qaa_option_fk')
                ->references('id')
                ->on('education_quiz_options')
                ->nullOnDelete();

            $table->unique(
                ['education_quiz_attempt_id', 'education_quiz_question_id'],
                'edu_qaa_attempt_question_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_quiz_attempt_answers');
    }
};
