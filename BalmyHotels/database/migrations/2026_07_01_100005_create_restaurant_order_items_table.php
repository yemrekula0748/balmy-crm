<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('restaurant_orders')->cascadeOnDelete();
            $table->foreignId('qr_menu_item_id')->nullable()->constrained('qr_menu_items')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_order_items');
    }
};
