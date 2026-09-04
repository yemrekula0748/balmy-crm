<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('roles')->where('name', 'bilgiislem')->exists()) {
            return;
        }

        DB::table('role_permissions')->updateOrInsert(
            ['role_name' => 'bilgiislem', 'module' => 'it_phishing_tests'],
            [
                'can_index' => true,
                'can_show' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('role_permissions')
            ->where('role_name', 'bilgiislem')
            ->where('module', 'it_phishing_tests')
            ->delete();
    }
};
