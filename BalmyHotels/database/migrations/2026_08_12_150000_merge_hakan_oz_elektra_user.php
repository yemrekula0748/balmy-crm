<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $canonical = DB::table('users')
            ->where('email', 'engineering@balmyforesta.com')
            ->first();
        $duplicate = DB::table('users')
            ->where('elektra_tenant_id', '32904')
            ->where('elektra_company_id', '3617')
            ->where('elektra_sicil_id', '1530194')
            ->first();

        if (!$canonical || !$duplicate || $canonical->id === $duplicate->id) {
            return;
        }

        DB::transaction(function () use ($canonical, $duplicate) {
            $roles = DB::table('user_roles')
                ->where('user_id', $duplicate->id)
                ->pluck('role_name')
                ->push('ogrenen')
                ->unique();

            foreach ($roles as $role) {
                DB::table('user_roles')->insertOrIgnore([
                    'user_id' => $canonical->id,
                    'role_name' => $role,
                ]);
            }

            DB::table('pdks_employees')
                ->where('user_id', $duplicate->id)
                ->update(['user_id' => $canonical->id]);

            DB::table('users')->where('id', $duplicate->id)->delete();

            DB::table('users')->where('id', $canonical->id)->update([
                'name' => $duplicate->name,
                'branch_id' => $duplicate->branch_id,
                'department_id' => $duplicate->department_id,
                'phone' => $duplicate->phone,
                'title' => $duplicate->title,
                'is_active' => $duplicate->is_active,
                'account_source' => 'elektra_linked',
                'elektra_tenant_id' => $duplicate->elektra_tenant_id,
                'elektra_company_id' => $duplicate->elektra_company_id,
                'elektra_sicil_id' => $duplicate->elektra_sicil_id,
                'identity_no_hash' => $duplicate->identity_no_hash,
                'phone_normalized' => $duplicate->phone_normalized,
                'elektra_synced_at' => $duplicate->elektra_synced_at,
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        // Birleştirilen iki kullanıcıyı ve geçmiş bağlantılarını güvenle ayırmak mümkün değildir.
    }
};
