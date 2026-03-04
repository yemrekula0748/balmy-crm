<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : fault_areas
 * Satır : 8
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedFaultAreasSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('fault_areas')->truncate();
        DB::table('fault_areas')->insert([
            [
            'id' => 2,
            'fault_location_id' => 2,
            'name' => '101',
            'is_active' => 1,
            'created_at' => '2026-02-27 19:31:09',
            'updated_at' => '2026-02-27 19:31:09'
            ],
            [
            'id' => 3,
            'fault_location_id' => 2,
            'name' => '102',
            'is_active' => 1,
            'created_at' => '2026-02-27 19:31:14',
            'updated_at' => '2026-02-27 19:31:14'
            ],
            [
            'id' => 4,
            'fault_location_id' => 2,
            'name' => '103',
            'is_active' => 1,
            'created_at' => '2026-02-27 19:31:18',
            'updated_at' => '2026-02-27 19:31:18'
            ],
            [
            'id' => 5,
            'fault_location_id' => 3,
            'name' => '202',
            'is_active' => 1,
            'created_at' => '2026-02-27 19:32:06',
            'updated_at' => '2026-02-27 19:32:06'
            ],
            [
            'id' => 6,
            'fault_location_id' => 3,
            'name' => '203',
            'is_active' => 1,
            'created_at' => '2026-02-27 19:32:11',
            'updated_at' => '2026-02-27 19:32:11'
            ],
            [
            'id' => 7,
            'fault_location_id' => 3,
            'name' => '204',
            'is_active' => 1,
            'created_at' => '2026-02-27 19:32:14',
            'updated_at' => '2026-02-27 19:32:14'
            ],
            [
            'id' => 8,
            'fault_location_id' => 3,
            'name' => '205',
            'is_active' => 1,
            'created_at' => '2026-02-27 19:32:18',
            'updated_at' => '2026-02-27 19:32:18'
            ],
            [
            'id' => 9,
            'fault_location_id' => 3,
            'name' => '206',
            'is_active' => 1,
            'created_at' => '2026-02-27 19:32:22',
            'updated_at' => '2026-02-27 19:32:22'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}