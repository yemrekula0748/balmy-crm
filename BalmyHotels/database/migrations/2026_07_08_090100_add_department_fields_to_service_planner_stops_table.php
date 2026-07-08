<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_planner_stops', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('passenger_name')->constrained()->nullOnDelete();
            $table->string('department_name')->nullable()->after('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('service_planner_stops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn('department_name');
        });
    }
};
