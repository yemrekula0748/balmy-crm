<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_course_id')->constrained('education_courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('language', 5)->index();
            $table->date('assigned_week_start')->index();
            $table->timestamp('due_at')->nullable();
            $table->unsignedInteger('watched_seconds')->default(0);
            $table->unsignedInteger('max_watched_seconds')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->string('status', 20)->default('not_started')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['education_course_id', 'user_id', 'language', 'assigned_week_start'],
                'education_assignment_unique'
            );
            $table->index(['user_id', 'status', 'assigned_week_start'], 'education_assignment_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_assignments');
    }
};
