<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : door_logs
 * Satır : 17
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedDoorLogsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('door_logs')->truncate();
        DB::table('door_logs')->insert([
            [
            'id' => 1,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'giris',
            'logged_at' => '2026-02-23 20:35:34',
            'notes' => null,
            'created_at' => '2026-02-23 20:35:34',
            'updated_at' => '2026-02-23 20:35:34'
            ],
            [
            'id' => 2,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'cikis',
            'logged_at' => '2026-02-23 20:35:53',
            'notes' => null,
            'created_at' => '2026-02-23 20:35:53',
            'updated_at' => '2026-02-23 20:35:53'
            ],
            [
            'id' => 3,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'giris',
            'logged_at' => '2026-02-24 07:28:00',
            'notes' => null,
            'created_at' => '2026-02-24 07:29:07',
            'updated_at' => '2026-02-24 07:29:07'
            ],
            [
            'id' => 4,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'cikis',
            'logged_at' => '2026-02-24 07:29:15',
            'notes' => null,
            'created_at' => '2026-02-24 07:29:15',
            'updated_at' => '2026-02-24 07:29:15'
            ],
            [
            'id' => 5,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'giris',
            'logged_at' => '2026-02-24 07:48:31',
            'notes' => null,
            'created_at' => '2026-02-24 07:48:31',
            'updated_at' => '2026-02-24 07:48:31'
            ],
            [
            'id' => 6,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'giris',
            'logged_at' => '2026-03-01 20:38:07',
            'notes' => null,
            'created_at' => '2026-03-01 20:38:07',
            'updated_at' => '2026-03-01 20:38:07'
            ],
            [
            'id' => 8,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'cikis',
            'logged_at' => '2026-03-01 20:54:47',
            'notes' => null,
            'created_at' => '2026-03-01 20:54:47',
            'updated_at' => '2026-03-01 20:54:47'
            ],
            [
            'id' => 9,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'giris',
            'logged_at' => '2026-03-01 20:58:35',
            'notes' => null,
            'created_at' => '2026-03-01 20:58:35',
            'updated_at' => '2026-03-01 20:58:35'
            ],
            [
            'id' => 10,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'cikis',
            'logged_at' => '2026-03-01 20:58:56',
            'notes' => null,
            'created_at' => '2026-03-01 20:58:56',
            'updated_at' => '2026-03-01 20:58:56'
            ],
            [
            'id' => 11,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'giris',
            'logged_at' => '2026-03-02 13:12:37',
            'notes' => null,
            'created_at' => '2026-03-02 13:12:37',
            'updated_at' => '2026-03-02 13:12:37'
            ],
            [
            'id' => 12,
            'user_id' => 5,
            'branch_id' => 2,
            'type' => 'giris',
            'logged_at' => '2026-03-02 15:44:19',
            'notes' => null,
            'created_at' => '2026-03-02 15:44:19',
            'updated_at' => '2026-03-02 15:44:19'
            ],
            [
            'id' => 13,
            'user_id' => 2,
            'branch_id' => 1,
            'type' => 'cikis',
            'logged_at' => '2026-03-02 15:44:28',
            'notes' => null,
            'created_at' => '2026-03-02 15:44:28',
            'updated_at' => '2026-03-02 15:44:28'
            ],
            [
            'id' => 14,
            'user_id' => 5,
            'branch_id' => 2,
            'type' => 'cikis',
            'logged_at' => '2026-03-02 15:51:28',
            'notes' => null,
            'created_at' => '2026-03-02 15:51:28',
            'updated_at' => '2026-03-02 15:51:28'
            ],
            [
            'id' => 15,
            'user_id' => 5,
            'branch_id' => 2,
            'type' => 'giris',
            'logged_at' => '2026-03-02 15:57:10',
            'notes' => null,
            'created_at' => '2026-03-02 15:57:10',
            'updated_at' => '2026-03-02 15:57:10'
            ],
            [
            'id' => 16,
            'user_id' => 5,
            'branch_id' => 2,
            'type' => 'cikis',
            'logged_at' => '2026-03-02 16:16:03',
            'notes' => null,
            'created_at' => '2026-03-02 16:16:03',
            'updated_at' => '2026-03-02 16:16:03'
            ],
            [
            'id' => 17,
            'user_id' => 5,
            'branch_id' => 2,
            'type' => 'giris',
            'logged_at' => '2026-03-03 08:04:31',
            'notes' => null,
            'created_at' => '2026-03-03 08:04:31',
            'updated_at' => '2026-03-03 08:04:31'
            ],
            [
            'id' => 18,
            'user_id' => 5,
            'branch_id' => 2,
            'type' => 'cikis',
            'logged_at' => '2026-03-03 08:05:46',
            'notes' => null,
            'created_at' => '2026-03-03 08:05:46',
            'updated_at' => '2026-03-03 08:05:46'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}