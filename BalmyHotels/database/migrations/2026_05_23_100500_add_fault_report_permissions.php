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
                'fault_room_reports' => [1, 1, 0, 0, 0],
                'fault_type_reports' => [1, 1, 0, 0, 0],
            ],
            'dept_manager' => [
                'fault_room_reports' => [1, 1, 0, 0, 0],
                'fault_type_reports' => [1, 1, 0, 0, 0],
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
            ->whereIn('module', ['fault_room_reports', 'fault_type_reports'])
            ->delete();
    }
};
