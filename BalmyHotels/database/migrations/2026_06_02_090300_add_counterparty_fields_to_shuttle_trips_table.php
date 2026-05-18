<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shuttle_trips', function (Blueprint $table) {
            $table->foreignId('destination_branch_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('branches')
                ->nullOnDelete();

            $table->time('origin_departure_time')
                ->nullable()
                ->after('trip_date');

            $table->unsignedSmallInteger('origin_departure_count')
                ->default(0)
                ->after('origin_departure_time');

            $table->index(['destination_branch_id', 'trip_date'], 'shuttle_trips_destination_branch_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('shuttle_trips', function (Blueprint $table) {
            $table->dropIndex('shuttle_trips_destination_branch_date_idx');
            $table->dropConstrainedForeignId('destination_branch_id');
            $table->dropColumn([
                'origin_departure_time',
                'origin_departure_count',
            ]);
        });
    }
};
