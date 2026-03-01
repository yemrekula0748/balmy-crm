<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('name');                    // {tr:"...", en:"...", ...}
            $table->json('description')->nullable(); // {tr:"...", en:"..."}
            $table->json('ingredients')->nullable(); // {tr:["..."], en:["..."]}
            $table->unsignedSmallInteger('calories')->nullable();
            $table->json('allergens')->nullable();   // ["gluten","milk",...]
            $table->string('category', 40)->nullable();
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_halal')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_labels');
    }
};
