<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        $now = now();
        $defaults = [
            'branch_manager' => [
                'animation_events' => [1, 1, 1, 0, 1],
                'event_tracking' => [1, 1, 1, 0, 0],
                'event_show_reports' => [1, 1, 0, 0, 0],
            ],
        ];

        foreach ($defaults as $roleName => $modules) {
            foreach ($modules as $module => $flags) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_name' => $roleName, 'module' => $module],
                    [
                        'can_index' => (bool) $flags[0],
                        'can_show' => (bool) $flags[1],
                        'can_create' => (bool) $flags[2],
                        'can_edit' => (bool) $flags[3],
                        'can_delete' => (bool) $flags[4],
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        DB::table('role_permissions')
            ->whereIn('module', ['animation_events', 'event_tracking', 'event_show_reports'])
            ->delete();
    }
};
