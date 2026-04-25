<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shuttle_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('plate', 20)->nullable();
            $table->enum('type', ['minibus', 'midibus', 'otobus', 'diger'])->default('minibus');
            $table->unsignedSmallInteger('capacity')->default(0)->comment('Maksimum yolcu kapasitesi');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shuttle_vehicles');
    }
};
