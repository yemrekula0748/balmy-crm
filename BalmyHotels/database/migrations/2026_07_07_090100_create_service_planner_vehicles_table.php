<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_planner_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_planner_plan_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('seat_capacity');
            $table->unsignedSmallInteger('vehicle_order')->default(1);
            $table->string('color', 20)->nullable();
            $table->timestamps();

            $table->index(['service_planner_plan_id', 'vehicle_order'], 'service_planner_vehicle_plan_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_planner_vehicles');
    }
};
