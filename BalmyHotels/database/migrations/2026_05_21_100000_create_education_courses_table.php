<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('language', 5)->index();
            $table->string('video_path');
            $table->string('video_original_name')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['trainer_id', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_courses');
    }
};
