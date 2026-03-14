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
        Schema::table('food_products', function (Blueprint $table) {
            $table->json('ingredients')->nullable()->after('allergens'); // çok dilli {"tr":"...","en":"..."}
            $table->decimal('calories', 8, 2)->nullable()->after('ingredients');
            $table->decimal('protein',  8, 2)->nullable()->after('calories');
            $table->decimal('carbs',    8, 2)->nullable()->after('protein');
            $table->decimal('fat',      8, 2)->nullable()->after('carbs');
        });
    }

    public function down(): void
    {
        Schema::table('food_products', function (Blueprint $table) {
            $table->dropColumn(['ingredients', 'calories', 'protein', 'carbs', 'fat']);
        });
    }
};
