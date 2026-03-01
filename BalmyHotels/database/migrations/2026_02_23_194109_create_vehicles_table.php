<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('plate')->unique();    // 34 ABC 123
            $table->string('brand');              // Mercedes
            $table->string('model');              // Vito
            $table->smallInteger('year');         // 2022
            $table->string('color')->nullable();  // Beyaz
            $table->string('type')->default('minibus'); // binek|minibus|kamyonet|kamyon
            $table->integer('current_km')->default(0);
            $table->date('license_expiry')->nullable();  // Ruhsat yenileme
            $table->string('chassis_no')->nullable();
            $table->string('engine_no')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
