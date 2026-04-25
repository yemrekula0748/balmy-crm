<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : vehicle_operations
 * Satır : 1
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedVehicleOperationsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('vehicle_operations')->truncate();
        DB::table('vehicle_operations')->insert([
            [
            'id' => 1,
            'vehicle_id' => 1,
            'user_id' => 2,
            'type' => 'giris',
            'km' => 250000,
            'operation_at' => '2026-02-24 07:31:00',
            'destination' => null,
            'notes' => null,
            'created_at' => '2026-02-24 07:31:10',
            'updated_at' => '2026-02-24 07:31:10'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}