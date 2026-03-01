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
        Schema::create('qr_menu_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_menu_id')->constrained('qr_menus')->cascadeOnDelete();
            $table->string('code', 10);       // tr, en, de, ar, ru...
            $table->string('name', 50);       // Türkçe, English, Deutsch...
            $table->string('flag', 10);       // 🇹🇷 emoji veya "tr" kodu
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['qr_menu_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_menu_languages');
    }
};
