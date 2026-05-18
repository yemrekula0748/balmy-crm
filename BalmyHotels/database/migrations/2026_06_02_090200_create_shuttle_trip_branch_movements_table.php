<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shuttle_trip_branch_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shuttle_trip_id')->constrained('shuttle_trips')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->enum('movement_type', ['arrival', 'departure']);
            $table->unsignedSmallInteger('headcount')->default(0);
            $table->timestamps();

            $table->unique(
                ['shuttle_trip_id', 'branch_id', 'movement_type'],
                'shuttle_trip_branch_movement_unique'
            );
            $table->index(['branch_id', 'movement_type'], 'shuttle_trip_branch_movement_branch_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shuttle_trip_branch_movements');
    }
};
