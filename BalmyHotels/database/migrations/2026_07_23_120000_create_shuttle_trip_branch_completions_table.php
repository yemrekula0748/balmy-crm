<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shuttle_trip_branch_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shuttle_trip_id')->constrained('shuttle_trips')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('completed_with_missing_data')->default(false);
            $table->timestamps();

            $table->unique(['shuttle_trip_id', 'branch_id'], 'shuttle_trip_branch_completion_unique');
            $table->index(['branch_id', 'completed_at'], 'shuttle_trip_branch_completion_branch_completed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shuttle_trip_branch_completions');
    }
};
