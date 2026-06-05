<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $modules = [
        'pdks_dashboard',
        'pdks_employees',
        'pdks_attendance',
        'pdks_breaks',
        'pdks_shifts',
        'pdks_leaves',
        'pdks_overtime',
        'pdks_reports',
        'pdks_notifications',
        'pdks_settings',
    ];

    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            $this->role('pdks_personel', 'PDKS Personel', 'secondary');
            $this->role('pdks_sorumlusu', 'PDKS Sorumlusu', 'primary');
        }

        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        $this->permissions('pdks_personel', [
            'pdks_dashboard' => ['index' => 1, 'show' => 1, 'create' => 1, 'edit' => 1, 'delete' => 0],
            'pdks_shifts' => ['index' => 1, 'show' => 1, 'create' => 0, 'edit' => 0, 'delete' => 0],
            'pdks_leaves' => ['index' => 1, 'show' => 1, 'create' => 0, 'edit' => 0, 'delete' => 0],
            'pdks_overtime' => ['index' => 1, 'show' => 1, 'create' => 0, 'edit' => 0, 'delete' => 0],
            'pdks_notifications' => ['index' => 1, 'show' => 1, 'create' => 0, 'edit' => 1, 'delete' => 0],
        ]);

        $managerPerms = [];
        foreach ($this->modules as $module) {
            $managerPerms[$module] = ['index' => 1, 'show' => 1, 'create' => 1, 'edit' => 1, 'delete' => 0];
        }
        $this->permissions('pdks_sorumlusu', $managerPerms);
        $this->permissions('insan_kaynaklari', $managerPerms);

        $branchManagerPerms = $managerPerms;
        $branchManagerPerms['pdks_settings'] = ['index' => 1, 'show' => 1, 'create' => 0, 'edit' => 0, 'delete' => 0];
        $this->permissions('branch_manager', $branchManagerPerms);

        $deptManagerPerms = [
            'pdks_dashboard' => ['index' => 1, 'show' => 1, 'create' => 1, 'edit' => 1, 'delete' => 0],
            'pdks_attendance' => ['index' => 1, 'show' => 1, 'create' => 0, 'edit' => 0, 'delete' => 0],
            'pdks_shifts' => ['index' => 1, 'show' => 1, 'create' => 1, 'edit' => 1, 'delete' => 0],
            'pdks_leaves' => ['index' => 1, 'show' => 1, 'create' => 0, 'edit' => 1, 'delete' => 0],
            'pdks_overtime' => ['index' => 1, 'show' => 1, 'create' => 0, 'edit' => 1, 'delete' => 0],
            'pdks_reports' => ['index' => 1, 'show' => 1, 'create' => 0, 'edit' => 0, 'delete' => 0],
            'pdks_notifications' => ['index' => 1, 'show' => 1, 'create' => 0, 'edit' => 1, 'delete' => 0],
        ];
        $this->permissions('dept_manager', $deptManagerPerms);
    }

    public function down(): void
    {
        if (Schema::hasTable('role_permissions')) {
            DB::table('role_permissions')->whereIn('module', $this->modules)->delete();
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->whereIn('name', ['pdks_personel', 'pdks_sorumlusu'])->delete();
        }
    }

    private function role(string $name, string $displayName, string $color): void
    {
        DB::table('roles')->updateOrInsert(
            ['name' => $name],
            ['display_name' => $displayName, 'color' => $color, 'is_system' => false, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    private function permissions(string $roleName, array $permissions): void
    {
        foreach ($permissions as $module => $actions) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_name' => $roleName, 'module' => $module],
                [
                    'can_index' => (bool) ($actions['index'] ?? 0),
                    'can_show' => (bool) ($actions['show'] ?? 0),
                    'can_create' => (bool) ($actions['create'] ?? 0),
                    'can_edit' => (bool) ($actions['edit'] ?? 0),
                    'can_delete' => (bool) ($actions['delete'] ?? 0),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
};
