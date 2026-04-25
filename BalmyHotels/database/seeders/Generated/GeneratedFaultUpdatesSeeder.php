<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : fault_updates
 * Satır : 4
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedFaultUpdatesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('fault_updates')->truncate();
        DB::table('fault_updates')->insert([
            [
            'id' => 6,
            'fault_id' => 3,
            'user_id' => 4,
            'note' => 'Arıza kaydı oluşturuldu.',
            'status_from' => null,
            'status_to' => 'open',
            'created_at' => '2026-03-02 17:01:23',
            'updated_at' => '2026-03-02 17:01:23'
            ],
            [
            'id' => 7,
            'fault_id' => 3,
            'user_id' => 6,
            'note' => 'işleme alındı',
            'status_from' => 'open',
            'status_to' => 'in_progress',
            'created_at' => '2026-03-02 17:08:05',
            'updated_at' => '2026-03-02 17:08:05'
            ],
            [
            'id' => 8,
            'fault_id' => 3,
            'user_id' => 6,
            'note' => 'Problem fixed',
            'status_from' => 'in_progress',
            'status_to' => 'closed',
            'created_at' => '2026-03-02 17:08:57',
            'updated_at' => '2026-03-02 17:08:57'
            ],
            [
            'id' => 9,
            'fault_id' => 4,
            'user_id' => 4,
            'note' => 'Arıza kaydı oluşturuldu.',
            'status_from' => null,
            'status_to' => 'open',
            'created_at' => '2026-03-02 17:13:47',
            'updated_at' => '2026-03-02 17:13:47'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}