<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : departments
 * Satır : 12
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedDepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('departments')->truncate();
        DB::table('departments')->insert([
            [
            'id' => 1,
            'branch_id' => 1,
            'name' => 'IT Departmanı',
            'color' => '#ff0000',
            'is_active' => 1,
            'created_at' => '2026-02-23 20:30:08',
            'updated_at' => '2026-02-24 12:47:31',
            'fault_assignable' => 1
            ],
            [
            'id' => 2,
            'branch_id' => 1,
            'name' => 'HK',
            'color' => '#492c9b',
            'is_active' => 1,
            'created_at' => '2026-02-24 07:28:49',
            'updated_at' => '2026-03-02 12:54:51',
            'fault_assignable' => 1
            ],
            [
            'id' => 3,
            'branch_id' => 2,
            'name' => 'Güvenlik',
            'color' => '#c19b77',
            'is_active' => 1,
            'created_at' => '2026-03-02 12:52:20',
            'updated_at' => '2026-03-02 12:52:20',
            'fault_assignable' => 0
            ],
            [
            'id' => 4,
            'branch_id' => 2,
            'name' => 'HK',
            'color' => '#001eff',
            'is_active' => 1,
            'created_at' => '2026-03-02 12:55:07',
            'updated_at' => '2026-03-02 12:55:19',
            'fault_assignable' => 1
            ],
            [
            'id' => 6,
            'branch_id' => 2,
            'name' => 'F&B',
            'color' => '#ff0000',
            'is_active' => 1,
            'created_at' => '2026-03-02 12:56:51',
            'updated_at' => '2026-03-02 12:56:51',
            'fault_assignable' => 0
            ],
            [
            'id' => 7,
            'branch_id' => 2,
            'name' => 'Ön Büro',
            'color' => '#44ff00',
            'is_active' => 1,
            'created_at' => '2026-03-02 13:02:46',
            'updated_at' => '2026-03-02 13:02:46',
            'fault_assignable' => 0
            ],
            [
            'id' => 8,
            'branch_id' => 2,
            'name' => 'Teknik Servis',
            'color' => '#ff00c8',
            'is_active' => 1,
            'created_at' => '2026-03-02 13:03:13',
            'updated_at' => '2026-03-02 13:03:13',
            'fault_assignable' => 1
            ],
            [
            'id' => 9,
            'branch_id' => 2,
            'name' => 'Misafir İlişkileri',
            'color' => '#2e8558',
            'is_active' => 1,
            'created_at' => '2026-03-02 13:03:35',
            'updated_at' => '2026-03-02 13:03:35',
            'fault_assignable' => 0
            ],
            [
            'id' => 10,
            'branch_id' => 2,
            'name' => 'Kalite',
            'color' => '#c19b77',
            'is_active' => 1,
            'created_at' => '2026-03-02 13:04:05',
            'updated_at' => '2026-03-02 13:04:05',
            'fault_assignable' => 0
            ],
            [
            'id' => 11,
            'branch_id' => 2,
            'name' => 'Animasyon',
            'color' => '#c19b77',
            'is_active' => 1,
            'created_at' => '2026-03-02 13:04:23',
            'updated_at' => '2026-03-02 13:04:23',
            'fault_assignable' => 0
            ],
            [
            'id' => 12,
            'branch_id' => 2,
            'name' => 'Bilgi İşlem',
            'color' => '#c19b77',
            'is_active' => 1,
            'created_at' => '2026-03-02 16:58:11',
            'updated_at' => '2026-03-02 16:58:11',
            'fault_assignable' => 1
            ],
            [
            'id' => 13,
            'branch_id' => 1,
            'name' => 'Bilgi İşlem',
            'color' => '#c19b77',
            'is_active' => 1,
            'created_at' => '2026-03-02 16:58:28',
            'updated_at' => '2026-03-02 16:58:28',
            'fault_assignable' => 1
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}