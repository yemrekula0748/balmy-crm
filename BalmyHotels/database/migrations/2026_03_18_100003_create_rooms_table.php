<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 20)->unique();
            $table->string('floor', 20)->nullable();
            $table->string('block', 50)->nullable();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $table->text('extra_features')->nullable();
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
        });

        // Pivot: odalar <-> yatak tipleri (çoklu yatak tipi)
        Schema::create('bed_type_room', function (Blueprint $table) {
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('bed_type_id')->constrained('bed_types')->cascadeOnDelete();
            $table->primary(['room_id', 'bed_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bed_type_room');
        Schema::dropIfExists('rooms');
    }
};
