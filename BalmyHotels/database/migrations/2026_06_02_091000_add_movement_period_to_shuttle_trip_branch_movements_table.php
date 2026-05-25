<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shuttle_trip_branch_movements')) {
            return;
        }

        Schema::table('shuttle_trip_branch_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('shuttle_trip_branch_movements', 'movement_period')) {
                $table->enum('movement_period', ['day', 'evening'])
                    ->default('day')
                    ->after('branch_id');
            }
        });

        if ($this->indexExists('shuttle_trip_branch_movement_unique')) {
            Schema::table('shuttle_trip_branch_movements', function (Blueprint $table) {
                $table->dropUnique('shuttle_trip_branch_movement_unique');
            });
        }

        if (! $this->indexExists('shuttle_trip_branch_period_movement_unique')) {
            Schema::table('shuttle_trip_branch_movements', function (Blueprint $table) {
                $table->unique(
                    ['shuttle_trip_id', 'branch_id', 'movement_period', 'movement_type'],
                    'shuttle_trip_branch_period_movement_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('shuttle_trip_branch_movements')) {
            return;
        }

        if ($this->indexExists('shuttle_trip_branch_period_movement_unique')) {
            Schema::table('shuttle_trip_branch_movements', function (Blueprint $table) {
                $table->dropUnique('shuttle_trip_branch_period_movement_unique');
            });
        }

        if (Schema::hasColumn('shuttle_trip_branch_movements', 'movement_period')) {
            DB::table('shuttle_trip_branch_movements')
                ->where('movement_period', '!=', 'day')
                ->delete();
        }

        if (! $this->indexExists('shuttle_trip_branch_movement_unique')) {
            Schema::table('shuttle_trip_branch_movements', function (Blueprint $table) {
                $table->unique(
                    ['shuttle_trip_id', 'branch_id', 'movement_type'],
                    'shuttle_trip_branch_movement_unique'
                );
            });
        }

        Schema::table('shuttle_trip_branch_movements', function (Blueprint $table) {
            if (Schema::hasColumn('shuttle_trip_branch_movements', 'movement_period')) {
                $table->dropColumn('movement_period');
            }
        });
    }

    private function indexExists(string $indexName): bool
    {
        $index = DB::selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [DB::getTablePrefix() . 'shuttle_trip_branch_movements', $indexName]
        );

        return $index !== null;
    }
};
