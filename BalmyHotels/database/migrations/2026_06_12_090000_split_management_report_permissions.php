<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_permissions')) {
            return;
        }

        $timestamp = now();
        $roles = DB::table('roles')->pluck('name');
        $modules = [
            'yonetim_mudur_giris_cikis_raporu',
            'yonetim_teknik_ariza_raporu',
            'yonetim_servis_raporu',
        ];

        foreach ($roles as $roleName) {
            $legacyBoardAccess = (bool) DB::table('role_permissions')
                ->where('role_name', $roleName)
                ->where('module', 'yonetim_kurulu_rapor')
                ->value('can_index');

            foreach ($modules as $module) {
                $existingAccess = (bool) DB::table('role_permissions')
                    ->where('role_name', $roleName)
                    ->where('module', $module)
                    ->value('can_index');

                $canView = $existingAccess
                    || $legacyBoardAccess
                    || in_array($roleName, ['super_admin', 'branch_manager'], true);

                DB::table('role_permissions')->updateOrInsert(
                    [
                        'role_name' => $roleName,
                        'module' => $module,
                    ],
                    [
                        'can_index' => $canView,
                        'can_show' => false,
                        'can_create' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
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
            ->whereIn('module', [
                'yonetim_mudur_giris_cikis_raporu',
                'yonetim_teknik_ariza_raporu',
                'yonetim_servis_raporu',
            ])
            ->delete();
    }
};
