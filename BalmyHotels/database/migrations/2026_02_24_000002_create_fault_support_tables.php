<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Konumlar (Odalar, Resepsiyon, Restoran vb.)
        Schema::create('fault_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');  // Örn: "Odalar", "Resepsiyon"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Alanlar (101, 102, 103 vb. — konuma bağlı)
        Schema::create('fault_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fault_location_id')->constrained('fault_locations')->cascadeOnDelete();
            $table->string('name');  // Örn: "101", "102"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Arıza Türleri
        Schema::create('fault_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete(); // null = tüm şubeler
            $table->string('name');                 // Örn: "Elektrik Arızası"
            $table->unsignedInteger('completion_hours')->default(24); // Tamamlanma süresi (saat)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fault_areas');
        Schema::dropIfExists('fault_locations');
        Schema::dropIfExists('fault_types');
    }
};
