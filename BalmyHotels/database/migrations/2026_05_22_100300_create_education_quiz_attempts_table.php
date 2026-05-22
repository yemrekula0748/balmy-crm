<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_assignment_id')->constrained('education_assignments')->cascadeOnDelete();
            $table->foreignId('education_course_id')->constrained('education_courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedInteger('min_correct')->default(0);
            $table->boolean('passed')->default(false)->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['education_assignment_id', 'submitted_at'], 'education_quiz_attempt_assignment_idx');
            $table->index(['education_course_id', 'passed'], 'education_quiz_attempt_course_passed_idx');
            $table->index(['user_id', 'passed'], 'education_quiz_attempt_user_passed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_quiz_attempts');
    }
};
