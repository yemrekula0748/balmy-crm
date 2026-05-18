<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shuttle_route_vehicle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shuttle_vehicle_id')->constrained('shuttle_vehicles')->cascadeOnDelete();
            $table->foreignId('shuttle_route_id')->constrained('shuttle_routes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shuttle_vehicle_id', 'shuttle_route_id'], 'shuttle_route_vehicle_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shuttle_route_vehicle');
    }
};
