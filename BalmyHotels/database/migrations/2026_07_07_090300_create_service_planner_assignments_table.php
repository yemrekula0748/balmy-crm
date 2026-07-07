<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_planner_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_planner_vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_planner_stop_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('stop_order');
            $table->decimal('leg_distance_km', 8, 2)->nullable();
            $table->decimal('cumulative_distance_km', 8, 2)->nullable();
            $table->unsignedInteger('travel_minutes')->nullable();
            $table->timestamps();

            $table->unique('service_planner_stop_id');
            $table->index(['service_planner_vehicle_id', 'stop_order'], 'service_planner_assignment_vehicle_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_planner_assignments');
    }
};
