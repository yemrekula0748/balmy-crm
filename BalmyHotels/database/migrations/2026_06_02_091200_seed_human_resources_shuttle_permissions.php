<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        if (Schema::hasTable('roles')) {
            DB::table('roles')->updateOrInsert(
                ['name' => 'insan_kaynaklari'],
                [
                    'display_name' => 'Insan Kaynaklari',
                    'color' => 'success',
                    'is_system' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if (! Schema::hasTable('role_permissions')) {
            return;
        }

        $roleNames = ['insan_kaynaklari', 'ik', 'hr', 'human_resources'];
        $permissions = [
            'shuttle_reports' => ['index' => true, 'show' => false, 'create' => false, 'edit' => false, 'delete' => false],
            'shuttle_routes' => ['index' => true, 'show' => false, 'create' => true, 'edit' => false, 'delete' => false],
            'shuttle_vehicles' => ['index' => true, 'show' => false, 'create' => true, 'edit' => false, 'delete' => false],
            'shuttle_operations' => ['index' => true, 'show' => false, 'create' => true, 'edit' => false, 'delete' => false],
        ];

        foreach ($roleNames as $roleName) {
            foreach ($permissions as $module => $actions) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_name' => $roleName, 'module' => $module],
                    [
                        'can_index' => $actions['index'],
                        'can_show' => $actions['show'],
                        'can_create' => $actions['create'],
                        'can_edit' => $actions['edit'],
                        'can_delete' => $actions['delete'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Deliberately left non-destructive: existing role permissions may be managed by admins.
    }
};
