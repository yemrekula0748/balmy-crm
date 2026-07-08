<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_planner_service_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('seat_capacity');
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['branch_id', 'is_active', 'sort_order'], 'service_planner_defs_branch_active_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_planner_service_definitions');
    }
};
