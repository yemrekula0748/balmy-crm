<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : roles
 * Satır : 6
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedRolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::table('roles')->insert([
            [
            'id' => 1,
            'name' => 'super_admin',
            'display_name' => 'Süper Admin',
            'color' => '#ed719e',
            'is_system' => 1,
            'created_at' => '2026-02-27 18:31:12',
            'updated_at' => '2026-02-27 19:28:15'
            ],
            [
            'id' => 2,
            'name' => 'branch_manager',
            'display_name' => 'Şube Müdürü',
            'color' => '#008cb4',
            'is_system' => 1,
            'created_at' => '2026-02-27 18:31:12',
            'updated_at' => '2026-02-27 19:28:08'
            ],
            [
            'id' => 3,
            'name' => 'dept_manager',
            'display_name' => 'Departman Müdürü',
            'color' => '#b51a00',
            'is_system' => 1,
            'created_at' => '2026-02-27 18:31:12',
            'updated_at' => '2026-02-27 19:28:02'
            ],
            [
            'id' => 4,
            'name' => 'staff',
            'display_name' => 'Personel',
            'color' => '#000000',
            'is_system' => 0,
            'created_at' => '2026-02-27 18:31:12',
            'updated_at' => '2026-02-27 19:27:51'
            ],
            [
            'id' => 5,
            'name' => 'teknik_ariza_personeli',
            'display_name' => 'Teknik Arıza Giriş (Personel)',
            'color' => '#669c35',
            'is_system' => 0,
            'created_at' => '2026-02-27 18:34:00',
            'updated_at' => '2026-02-27 19:27:39'
            ],
            [
            'id' => 6,
            'name' => 'anakapi',
            'display_name' => 'Ana Kapı',
            'color' => '#6c757d',
            'is_system' => 0,
            'created_at' => '2026-03-02 12:51:26',
            'updated_at' => '2026-03-02 12:51:26'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}