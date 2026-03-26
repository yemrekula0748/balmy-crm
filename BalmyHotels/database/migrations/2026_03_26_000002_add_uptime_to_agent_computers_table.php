<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_computers', function (Blueprint $table) {
            $table->unsignedBigInteger('uptime_seconds')->nullable()->after('last_boot_time');
            $table->string('uptime_display')->nullable()->after('uptime_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('agent_computers', function (Blueprint $table) {
            $table->dropColumn(['uptime_seconds', 'uptime_display']);
        });
    }
};
