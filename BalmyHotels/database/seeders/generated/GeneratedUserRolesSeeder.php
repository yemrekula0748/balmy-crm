<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : user_roles
 * Satır : 7
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedUserRolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('user_roles')->truncate();
        DB::table('user_roles')->insert([
            [
            'id' => 1,
            'user_id' => 1,
            'role_name' => 'super_admin'
            ],
            [
            'id' => 2,
            'user_id' => 2,
            'role_name' => 'super_admin'
            ],
            [
            'id' => 3,
            'user_id' => 3,
            'role_name' => 'teknik_ariza_personeli'
            ],
            [
            'id' => 5,
            'user_id' => 4,
            'role_name' => 'anakapi'
            ],
            [
            'id' => 6,
            'user_id' => 4,
            'role_name' => 'teknik_ariza_personeli'
            ],
            [
            'id' => 7,
            'user_id' => 5,
            'role_name' => 'dept_manager'
            ],
            [
            'id' => 8,
            'user_id' => 6,
            'role_name' => 'teknik_ariza_personeli'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}