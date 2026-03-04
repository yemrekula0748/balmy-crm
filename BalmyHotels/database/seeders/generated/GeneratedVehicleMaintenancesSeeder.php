<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : vehicle_maintenances
 * Satır : 1
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedVehicleMaintenancesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('vehicle_maintenances')->truncate();
        DB::table('vehicle_maintenances')->insert([
            [
            'id' => 1,
            'vehicle_id' => 1,
            'user_id' => 2,
            'type' => 'yag',
            'maintenance_at' => '2026-02-24 00:00:00',
            'km' => 250000,
            'next_km' => 3000000,
            'next_date' => null,
            'service_name' => null,
            'cost' => 1000000,
            'notes' => null,
            'created_at' => '2026-02-24 07:31:22',
            'updated_at' => '2026-02-24 07:31:22'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}