<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();  // Demirbaş kodu
            $table->foreignId('category_id')->constrained('asset_categories')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();  // Oda/alan
            $table->enum('status', ['available', 'in_use', 'maintenance', 'retired'])->default('available');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->string('serial_no')->nullable();
            $table->date('warranty_until')->nullable();
            $table->string('photo')->nullable();
            $table->json('properties')->nullable(); // Dinamik alanların değerleri
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
