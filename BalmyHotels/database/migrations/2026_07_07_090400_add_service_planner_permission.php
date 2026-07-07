<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('role_permissions')) {
            return;
        }

        $now = now();

        foreach (['insan_kaynaklari', 'ik', 'hr', 'human_resources'] as $roleName) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_name' => $roleName, 'module' => 'service_planner'],
                [
                    'can_index' => true,
                    'can_show' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('role_permissions')) {
            return;
        }

        DB::table('role_permissions')
            ->where('module', 'service_planner')
            ->whereIn('role_name', ['insan_kaynaklari', 'ik', 'hr', 'human_resources'])
            ->delete();
    }
};
