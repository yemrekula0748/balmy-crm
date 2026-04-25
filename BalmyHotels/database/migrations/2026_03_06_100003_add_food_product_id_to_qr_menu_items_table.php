<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_menu_items', function (Blueprint $table) {
            $table->foreignId('food_product_id')
                  ->nullable()
                  ->after('category_id')
                  ->constrained('food_products')
                  ->nullOnDelete();
            $table->decimal('price_override', 10, 2)
                  ->nullable()
                  ->after('price')
                  ->comment('Kütüphane ürününe özel menü fiyatı');
        });
    }

    public function down(): void
    {
        Schema::table('qr_menu_items', function (Blueprint $table) {
            $table->dropForeign(['food_product_id']);
            $table->dropColumn(['food_product_id', 'price_override']);
        });
    }
};
