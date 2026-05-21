<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_event_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_event_id')->constrained('education_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->text('note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['education_event_id', 'user_id'], 'education_event_response_unique');
            $table->index(['user_id', 'status'], 'education_event_response_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_event_responses');
    }
};
