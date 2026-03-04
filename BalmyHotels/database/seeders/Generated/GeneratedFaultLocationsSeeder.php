<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : fault_locations
 * Satır : 3
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedFaultLocationsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('fault_locations')->truncate();
        DB::table('fault_locations')->insert([
            [
            'id' => 1,
            'branch_id' => 1,
            'name' => 'Mutfak',
            'is_active' => 1,
            'created_at' => '2026-02-24 12:32:42',
            'updated_at' => '2026-02-24 12:32:42'
            ],
            [
            'id' => 2,
            'branch_id' => 2,
            'name' => 'A Blok Odalar',
            'is_active' => 1,
            'created_at' => '2026-02-27 19:30:55',
            'updated_at' => '2026-02-27 19:30:55'
            ],
            [
            'id' => 3,
            'branch_id' => 1,
            'name' => 'B Blok Odalar',
            'is_active' => 1,
            'created_at' => '2026-02-27 19:32:02',
            'updated_at' => '2026-02-27 19:32:02'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}