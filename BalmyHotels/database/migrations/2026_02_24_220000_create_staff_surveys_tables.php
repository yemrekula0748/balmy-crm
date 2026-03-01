<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Personel Anketleri
        Schema::create('staff_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('title');
            $table->json('description')->nullable();
            $table->string('slug', 80)->unique();
            $table->json('languages')->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->boolean('show_dept_field')->default(true);
            $table->boolean('show_employee_id_field')->default(false);
            $table->boolean('show_language_select')->default(false);
            $table->boolean('allow_multiple')->default(false); // aynı kişi birden fazla doldurabiliyor mu?
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2) Sorular
        Schema::create('staff_survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('staff_surveys')->cascadeOnDelete();
            $table->tinyInteger('sort_order')->unsigned()->default(0);
            $table->string('type', 20); // text, textarea, radio, checkbox, rating, nps, yesno
            $table->json('title');
            $table->json('options')->nullable(); // radio/checkbox seçenekleri: {"tr":["a","b"],"en":["a","b"]}
            $table->boolean('required')->default(false);
            // Koşullu gösterim
            $table->foreignId('condition_question_id')
                  ->nullable()
                  ->constrained('staff_survey_questions')
                  ->nullOnDelete();
            $table->string('condition_answer', 200)->nullable();
            $table->timestamps();
        });

        // 3) Yanıtlar (her oturum)
        Schema::create('staff_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('staff_surveys')->cascadeOnDelete();
            $table->string('respondent_name', 100)->nullable();
            $table->string('respondent_dept', 100)->nullable();
            $table->string('respondent_employee_id', 50)->nullable();
            $table->string('lang', 5)->default('tr');
            $table->string('respondent_token', 40)->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        // 4) Bireysel cevaplar
        Schema::create('staff_survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('staff_survey_responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('staff_survey_questions')->cascadeOnDelete();
            $table->text('answer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_survey_answers');
        Schema::dropIfExists('staff_survey_responses');
        Schema::dropIfExists('staff_survey_questions');
        Schema::dropIfExists('staff_surveys');
    }
};
