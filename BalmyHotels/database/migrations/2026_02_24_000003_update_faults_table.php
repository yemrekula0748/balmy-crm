<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faults', function (Blueprint $table) {
            // Yeni sütunlar
            $table->foreignId('fault_type_id')->nullable()->constrained('fault_types')->nullOnDelete()->after('assigned_department_id');
            $table->foreignId('fault_location_id')->nullable()->constrained('fault_locations')->nullOnDelete()->after('fault_type_id');
            $table->foreignId('fault_area_id')->nullable()->constrained('fault_areas')->nullOnDelete()->after('fault_location_id');
            $table->string('image_path')->nullable()->after('description');

            // location sütunu zaten nullable, title store'da fault_type'dan set edilecek
        });
    }

    public function down(): void
    {
        Schema::table('faults', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fault_type_id');
            $table->dropConstrainedForeignId('fault_location_id');
            $table->dropConstrainedForeignId('fault_area_id');
            $table->dropColumn('image_path');
        });
    }
};
