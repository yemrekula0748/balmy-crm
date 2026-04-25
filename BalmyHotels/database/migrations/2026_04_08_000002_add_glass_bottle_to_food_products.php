<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_products', function (Blueprint $table) {
            $table->decimal('price_glass',  10, 2)->nullable()->after('price');
            $table->decimal('price_bottle', 10, 2)->nullable()->after('price_glass');
            $table->unsignedSmallInteger('cl_glass')->nullable()->after('price_bottle');
            $table->unsignedSmallInteger('cl_bottle')->nullable()->after('cl_glass');
        });
    }

    public function down(): void
    {
        Schema::table('food_products', function (Blueprint $table) {
            $table->dropColumn(['price_glass', 'price_bottle', 'cl_glass', 'cl_bottle']);
        });
    }
};
