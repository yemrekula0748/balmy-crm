<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : assets
 * Satır : 2
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedAssetsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('assets')->truncate();
        DB::table('assets')->insert([
            [
            'id' => 1,
            'asset_code' => 'DMB-0001',
            'category_id' => 1,
            'branch_id' => 1,
            'name' => 'Samsung TV',
            'description' => null,
            'location' => 'Lobi',
            'status' => 'in_use',
            'purchase_date' => '2025-05-30 00:00:00',
            'purchase_price' => 10200,
            'serial_no' => 'SMS-1111111',
            'warranty_until' => '2026-02-01 00:00:00',
            'photo' => null,
            'properties' => '{"001":"001"}',
            'created_at' => '2026-02-23 21:11:53',
            'updated_at' => '2026-02-23 21:11:53'
            ],
            [
            'id' => 2,
            'asset_code' => 'DMB-0002',
            'category_id' => 3,
            'branch_id' => 1,
            'name' => 'Daniel Robinson',
            'description' => null,
            'location' => '209',
            'status' => 'in_use',
            'purchase_date' => '2026-02-13 00:00:00',
            'purchase_price' => 10000,
            'serial_no' => 'sadadasdasd',
            'warranty_until' => '2026-02-27 00:00:00',
            'photo' => null,
            'properties' => '{"001":"Siyah"}',
            'created_at' => '2026-02-24 07:50:25',
            'updated_at' => '2026-02-24 07:50:25'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}