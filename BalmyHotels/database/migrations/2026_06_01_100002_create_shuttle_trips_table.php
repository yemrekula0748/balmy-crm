<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shuttle_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shuttle_vehicle_id')->constrained('shuttle_vehicles')->cascadeOnDelete();
            $table->foreignId('route_id')->nullable()->constrained('shuttle_routes')->nullOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->enum('shift', [
                'A Shifti',
                'B Shifti',
                'C Shifti',
                'Ara Shift 12-9',
                'İdari',
                'Lojman',
            ])->comment('Vardiya');
            $table->date('trip_date');
            $table->time('arrival_time')->nullable()->comment('Geliş saati');
            $table->unsignedSmallInteger('arrival_count')->default(0)->comment('Gelen kişi sayısı');
            $table->time('departure_time')->nullable()->comment('Dönüş saati');
            $table->unsignedSmallInteger('departure_count')->default(0)->comment('Dönen kişi sayısı');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['branch_id', 'trip_date']);
            $table->index(['shuttle_vehicle_id', 'trip_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shuttle_trips');
    }
};
