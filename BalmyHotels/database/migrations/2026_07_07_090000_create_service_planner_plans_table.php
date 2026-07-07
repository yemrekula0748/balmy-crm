<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_planner_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->date('plan_date')->nullable();
            $table->string('start_location_name');
            $table->text('start_address');
            $table->decimal('start_latitude', 11, 7)->nullable();
            $table->decimal('start_longitude', 11, 7)->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamp('route_generated_at')->nullable();
            $table->string('geocoding_provider', 30)->nullable();
            $table->text('planning_notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'plan_date']);
            $table->index(['status', 'plan_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_planner_plans');
    }
};
