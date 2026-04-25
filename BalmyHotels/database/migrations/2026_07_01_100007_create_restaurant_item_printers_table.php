<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_item_printers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->foreignId('qr_menu_item_id')->constrained('qr_menu_items')->cascadeOnDelete();
            $table->foreignId('printer_id')->constrained('printers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['restaurant_id', 'qr_menu_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_item_printers');
    }
};
