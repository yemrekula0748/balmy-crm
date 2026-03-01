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
        Schema::create('qr_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name')->unique();      // slug: restoran1, lobby-bar
            $table->json('title');                 // {"tr":"Ana Restoran","en":"Main Restaurant"}
            $table->json('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('theme_color', 20)->default('#c19b77');
            $table->boolean('is_active')->default(true);
            $table->string('currency', 10)->default('TRY');
            $table->string('currency_symbol', 5)->default('₺');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_menus');
    }
};
