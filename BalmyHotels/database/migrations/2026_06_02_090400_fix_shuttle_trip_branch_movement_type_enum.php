<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shuttle_trip_branch_movements')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE shuttle_trip_branch_movements
            MODIFY COLUMN movement_type ENUM('pickup','dropoff','arrival','departure') NOT NULL
        ");

        DB::table('shuttle_trip_branch_movements')
            ->where('movement_type', 'pickup')
            ->update(['movement_type' => 'arrival']);

        DB::table('shuttle_trip_branch_movements')
            ->where('movement_type', 'dropoff')
            ->update(['movement_type' => 'departure']);

        DB::statement("
            ALTER TABLE shuttle_trip_branch_movements
            MODIFY COLUMN movement_type ENUM('arrival','departure') NOT NULL
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('shuttle_trip_branch_movements')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE shuttle_trip_branch_movements
            MODIFY COLUMN movement_type ENUM('pickup','dropoff','arrival','departure') NOT NULL
        ");

        DB::table('shuttle_trip_branch_movements')
            ->where('movement_type', 'arrival')
            ->update(['movement_type' => 'pickup']);

        DB::table('shuttle_trip_branch_movements')
            ->where('movement_type', 'departure')
            ->update(['movement_type' => 'dropoff']);

        DB::statement("
            ALTER TABLE shuttle_trip_branch_movements
            MODIFY COLUMN movement_type ENUM('pickup','dropoff') NOT NULL
        ");
    }
};
