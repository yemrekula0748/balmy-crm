<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shuttle_trip_branch_movements', function (Blueprint $table) {
            $table->time('movement_time')
                ->nullable()
                ->after('headcount');
        });

        DB::statement("
            UPDATE shuttle_trip_branch_movements movement
            INNER JOIN shuttle_trips trip ON trip.id = movement.shuttle_trip_id
            SET movement.movement_time = CASE
                WHEN movement.movement_type = 'arrival' THEN trip.arrival_time
                WHEN movement.movement_type = 'departure' THEN trip.departure_time
                ELSE NULL
            END
            WHERE movement.movement_time IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('shuttle_trip_branch_movements', function (Blueprint $table) {
            $table->dropColumn('movement_time');
        });
    }
};
