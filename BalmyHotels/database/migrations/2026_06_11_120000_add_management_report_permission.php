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

        foreach ($roles as $roleName) {
            $canView = in_array($roleName, ['super_admin', 'branch_manager'], true);
            $payload = [
                'can_index'  => $canView,
                'can_show'   => false,
                'can_create' => false,
                'can_edit'   => false,
                'can_delete' => false,
                'updated_at' => $timestamp,
            ];

            $exists = DB::table('role_permissions')
                ->where('role_name', $roleName)
                ->where('module', 'yonetim_kurulu_rapor')
                ->exists();

            if ($exists) {
                DB::table('role_permissions')
                    ->where('role_name', $roleName)
                    ->where('module', 'yonetim_kurulu_rapor')
                    ->update($payload);
            } else {
                DB::table('role_permissions')->insert(array_merge($payload, [
                    'role_name'  => $roleName,
                    'module'     => 'yonetim_kurulu_rapor',
                    'created_at' => $timestamp,
                ]));
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        DB::table('role_permissions')
            ->where('module', 'yonetim_kurulu_rapor')
            ->delete();
    }
};
