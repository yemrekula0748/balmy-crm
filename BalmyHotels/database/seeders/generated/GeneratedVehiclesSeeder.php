<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : vehicles
 * Satır : 1
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedVehiclesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('vehicles')->truncate();
        DB::table('vehicles')->insert([
            [
            'id' => 1,
            'branch_id' => 1,
            'plate' => '07 AAS 085',
            'brand' => 'MERCEDES-BENZ',
            'model' => 'Vito VIP',
            'year' => 2026,
            'color' => 'BEYAZ',
            'type' => 'binek',
            'current_km' => 250000,
            'license_expiry' => '2026-02-28 00:00:00',
            'chassis_no' => 'sadasdasd',
            'engine_no' => 'V224SSDADASDASDAD99',
            'notes' => 'a',
            'is_active' => 1,
            'created_at' => '2026-02-24 07:31:00',
            'updated_at' => '2026-02-24 07:31:00'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}