<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shuttle_trips', function (Blueprint $table) {
            $table->boolean('arrived_with_different_vehicle')
                ->default(false)
                ->after('departure_count');
            $table->boolean('is_transfer')
                ->default(false)
                ->after('arrived_with_different_vehicle');
        });
    }

    public function down(): void
    {
        Schema::table('shuttle_trips', function (Blueprint $table) {
            $table->dropColumn([
                'arrived_with_different_vehicle',
                'is_transfer',
            ]);
        });
    }
};
