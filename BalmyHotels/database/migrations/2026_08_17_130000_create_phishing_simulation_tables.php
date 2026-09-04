<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phishing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('status', 20)->default('active')->index();
            $table->string('redirect_url')->default('https://www.google.com/');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('phishing_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('phishing_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_name', 150);
            $table->string('target_email');
            $table->string('token', 64)->unique();
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('first_clicked_at')->nullable();
            $table->timestamp('last_clicked_at')->nullable();
            $table->unsignedInteger('credential_attempt_count')->default(0);
            $table->timestamp('first_credential_attempted_at')->nullable();
            $table->timestamp('last_credential_attempted_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'user_id']);
            $table->index(['campaign_id', 'first_clicked_at']);
            $table->index(['campaign_id', 'first_credential_attempted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phishing_targets');
        Schema::dropIfExists('phishing_campaigns');
    }
};
