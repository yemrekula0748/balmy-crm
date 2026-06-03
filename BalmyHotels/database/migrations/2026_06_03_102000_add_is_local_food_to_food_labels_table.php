<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('food_labels', 'is_local_food')) {
            return;
        }

        Schema::table('food_labels', function (Blueprint $table) {
            $table->boolean('is_local_food')
                ->default(false)
                ->after('is_halal');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('food_labels', 'is_local_food')) {
            return;
        }

        Schema::table('food_labels', function (Blueprint $table) {
            $table->dropColumn('is_local_food');
        });
    }
};
