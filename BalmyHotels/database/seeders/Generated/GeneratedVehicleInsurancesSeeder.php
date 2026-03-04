<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : vehicle_insurances
 * Satır : 1
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedVehicleInsurancesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('vehicle_insurances')->truncate();
        DB::table('vehicle_insurances')->insert([
            [
            'id' => 1,
            'vehicle_id' => 1,
            'type' => 'kasko',
            'company' => 'axa',
            'policy_no' => 'SADSADASDASDASD',
            'start_date' => '2026-02-23 00:00:00',
            'end_date' => '2026-02-24 00:00:00',
            'cost' => 23000,
            'notes' => null,
            'created_at' => '2026-02-24 07:31:38',
            'updated_at' => '2026-02-24 07:31:38'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}