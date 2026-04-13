<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_showcases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('subtitle')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('accent_color', 20)->default('#c19b77');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('menu_showcase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showcase_id')->constrained('menu_showcases')->cascadeOnDelete();
            $table->foreignId('qr_menu_id')->constrained('qr_menus')->cascadeOnDelete();
            $table->string('label')->nullable(); // override display name
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['showcase_id', 'qr_menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_showcase_items');
        Schema::dropIfExists('menu_showcases');
    }
};
