<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_quiz_question_id')->constrained('education_quiz_questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['education_quiz_question_id', 'sort_order'], 'education_quiz_options_question_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_quiz_options');
    }
};
