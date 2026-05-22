<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_course_id')->constrained('education_courses')->cascadeOnDelete();
            $table->text('question');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['education_course_id', 'sort_order'], 'education_quiz_questions_course_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_quiz_questions');
    }
};
