<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('education_courses', function (Blueprint $table) {
            $table->unsignedInteger('quiz_min_correct')->default(0)->after('duration_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('education_courses', function (Blueprint $table) {
            $table->dropColumn('quiz_min_correct');
        });
    }
};
