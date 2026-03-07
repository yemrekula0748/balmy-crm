<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('food_category_id')->nullable()->constrained('food_categories')->nullOnDelete();
            $table->json('title');                // {"tr":"Menemen","en":"Scrambled Eggs"}
            $table->json('description')->nullable(); // {"tr":"Açıklama","en":"Description"}
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->json('badges')->nullable();   // ["Vegan","Glutensiz"]
            // Dinamik opsiyonlar: [{"label":"Alerjenler","type":"tags","values":["Gluten","Süt"]},{"label":"Alkol Oranı","type":"text","value":"13%"}]
            $table->json('options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_products');
    }
};
