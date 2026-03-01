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
        Schema::create('qr_menu_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_menu_id')->constrained('qr_menus')->cascadeOnDelete();
            $table->json('title');             // {"tr":"Kahvaltı","en":"Breakfast"}
            $table->json('description')->nullable();
            $table->string('icon')->nullable(); // emoji veya ikon kodu: 🍳
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_menu_categories');
    }
};
