<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_planner_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_planner_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number')->nullable();
            $table->string('passenger_name');
            $table->string('phone', 40)->nullable();
            $table->string('district')->nullable();
            $table->text('address');
            $table->text('notes')->nullable();
            $table->decimal('latitude', 11, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->decimal('distance_to_start_km', 8, 2)->nullable();
            $table->string('geocode_status', 20)->default('pending');
            $table->string('geocode_provider', 30)->nullable();
            $table->string('geocode_message')->nullable();
            $table->timestamps();

            $table->index(['service_planner_plan_id', 'geocode_status'], 'service_planner_stop_plan_geocode_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_planner_stops');
    }
};
