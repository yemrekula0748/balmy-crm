<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_computer_security_snapshots', function (Blueprint $table) {
            $table->json('new_services_24h')->nullable()->after('windows_update');
            $table->json('account_changes_24h')->nullable()->after('new_services_24h');
            $table->json('unexpected_shutdowns')->nullable()->after('account_changes_24h');
            $table->json('rdp_events_24h')->nullable()->after('unexpected_shutdowns');
            $table->json('scheduled_tasks_24h')->nullable()->after('rdp_events_24h');
            $table->json('admin_logins_1h')->nullable()->after('scheduled_tasks_24h');
            $table->json('service_crashes_24h')->nullable()->after('admin_logins_1h');
        });
    }

    public function down(): void
    {
        Schema::table('agent_computer_security_snapshots', function (Blueprint $table) {
            $table->dropColumn([
                'new_services_24h',
                'account_changes_24h',
                'unexpected_shutdowns',
                'rdp_events_24h',
                'scheduled_tasks_24h',
                'admin_logins_1h',
                'service_crashes_24h',
            ]);
        });
    }
};
