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
        Schema::create('qr_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('qr_menu_categories')->cascadeOnDelete();
            $table->json('title');              // {"tr":"Menemen","en":"Turkish Scrambled Eggs"}
            $table->json('description')->nullable(); // malzeme/içerik bilgisi
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // öne çıkarılan ürün
            $table->json('badges')->nullable(); // ["Vegan","Gluten Free","Spicy"]
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_menu_items');
    }
};
