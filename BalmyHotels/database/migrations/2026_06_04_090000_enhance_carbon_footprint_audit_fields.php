<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carbon_footprint_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('carbon_footprint_reports', 'hotel_name')) {
                $table->string('hotel_name')->nullable()->after('title');
            }

            if (! Schema::hasColumn('carbon_footprint_reports', 'location')) {
                $table->string('location')->nullable()->after('hotel_name');
            }

            if (! Schema::hasColumn('carbon_footprint_reports', 'total_beds')) {
                $table->integer('total_beds')->default(0)->after('total_rooms');
            }

            if (! Schema::hasColumn('carbon_footprint_reports', 'open_area_sqm')) {
                $table->decimal('open_area_sqm', 10, 2)->default(0)->after('total_area_sqm');
            }

            if (! Schema::hasColumn('carbon_footprint_reports', 'occupancy_rate')) {
                $table->decimal('occupancy_rate', 5, 2)->default(0)->after('open_area_sqm');
            }

            if (! Schema::hasColumn('carbon_footprint_reports', 'average_stay_days')) {
                $table->decimal('average_stay_days', 8, 2)->default(0)->after('occupancy_rate');
            }

            if (! Schema::hasColumn('carbon_footprint_reports', 'female_staff_count')) {
                $table->integer('female_staff_count')->default(0)->after('staff_count');
            }

            if (! Schema::hasColumn('carbon_footprint_reports', 'male_staff_count')) {
                $table->integer('male_staff_count')->default(0)->after('female_staff_count');
            }

            if (! Schema::hasColumn('carbon_footprint_reports', 'factor_dataset_version')) {
                $table->string('factor_dataset_version')->nullable()->after('standards_applied');
            }

            if (! Schema::hasColumn('carbon_footprint_reports', 'verification_notes')) {
                $table->text('verification_notes')->nullable()->after('methodology_notes');
            }

            if (! Schema::hasColumn('carbon_footprint_reports', 'iso_14001_notes')) {
                $table->text('iso_14001_notes')->nullable()->after('verification_notes');
            }
        });

        Schema::table('carbon_footprint_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('carbon_footprint_entries', 'standard_code')) {
                $table->string('standard_code', 60)->nullable()->after('ef_source');
            }

            if (! Schema::hasColumn('carbon_footprint_entries', 'frequency')) {
                $table->string('frequency', 30)->nullable()->after('standard_code');
            }

            if (! Schema::hasColumn('carbon_footprint_entries', 'calculation_method')) {
                $table->text('calculation_method')->nullable()->after('frequency');
            }

            if (! Schema::hasColumn('carbon_footprint_entries', 'evidence_reference')) {
                $table->string('evidence_reference')->nullable()->after('calculation_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('carbon_footprint_entries', function (Blueprint $table) {
            foreach (['evidence_reference', 'calculation_method', 'frequency', 'standard_code'] as $column) {
                if (Schema::hasColumn('carbon_footprint_entries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('carbon_footprint_reports', function (Blueprint $table) {
            foreach ([
                'iso_14001_notes',
                'verification_notes',
                'factor_dataset_version',
                'male_staff_count',
                'female_staff_count',
                'average_stay_days',
                'occupancy_rate',
                'open_area_sqm',
                'total_beds',
                'location',
                'hotel_name',
            ] as $column) {
                if (Schema::hasColumn('carbon_footprint_reports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
