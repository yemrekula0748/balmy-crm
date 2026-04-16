<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_computers', function (Blueprint $table) {
            $table->string('wifi_ssid', 255)->nullable()->after('last_seen_at');
            $table->timestamp('last_screenshot_at')->nullable()->after('wifi_ssid');
        });
    }

    public function down(): void
    {
        Schema::table('agent_computers', function (Blueprint $table) {
            $table->dropColumn(['wifi_ssid', 'last_screenshot_at']);
        });
    }
};
