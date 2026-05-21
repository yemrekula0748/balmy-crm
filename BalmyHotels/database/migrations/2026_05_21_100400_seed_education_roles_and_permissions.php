<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('roles')->updateOrInsert(
            ['name' => 'egitmen'],
            [
                'display_name' => 'Egitmen',
                'color' => 'warning',
                'is_system' => false,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'ogrenen'],
            [
                'display_name' => 'Ogrenen',
                'color' => 'success',
                'is_system' => false,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $this->savePermissions('egitmen', [
            'education_courses' => [1, 1, 1, 1, 1],
            'education_assignments' => [1, 0, 1, 0, 1],
            'education_learning' => [1, 1, 0, 1, 0],
            'education_events' => [1, 1, 1, 1, 1],
            'education_reports' => [1, 0, 0, 0, 0],
        ], $now);

        $this->savePermissions('ogrenen', [
            'education_learning' => [1, 1, 0, 1, 0],
            'education_events' => [1, 1, 0, 0, 0],
        ], $now);

        $this->savePermissions('branch_manager', [
            'education_courses' => [1, 1, 1, 1, 1],
            'education_assignments' => [1, 1, 1, 1, 1],
            'education_learning' => [1, 1, 1, 1, 1],
            'education_events' => [1, 1, 1, 1, 1],
            'education_reports' => [1, 1, 1, 1, 1],
        ], $now);
    }

    public function down(): void
    {
        $modules = [
            'education_courses',
            'education_assignments',
            'education_learning',
            'education_events',
            'education_reports',
        ];

        DB::table('role_permissions')
            ->whereIn('module', $modules)
            ->whereIn('role_name', ['egitmen', 'ogrenen', 'branch_manager'])
            ->delete();

        DB::table('roles')->whereIn('name', ['egitmen', 'ogrenen'])->delete();
    }

    private function savePermissions(string $roleName, array $permissions, $now): void
    {
        foreach ($permissions as $module => $flags) {
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
};
