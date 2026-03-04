<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : asset_exits
 * Satır : 1
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedAssetExitsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('asset_exits')->truncate();
        DB::table('asset_exits')->insert([
            [
            'id' => 1,
            'asset_id' => 1,
            'branch_id' => 1,
            'taker_type' => 'staff',
            'staff_id' => 2,
            'guest_name' => null,
            'guest_room' => null,
            'guest_id_no' => null,
            'guest_phone' => null,
            'purpose' => 'evinde kullanmak için',
            'taken_at' => '2026-02-23 21:12:00',
            'expected_return_at' => '2026-02-26 03:16:00',
            'returned_at' => null,
            'status' => 'approved',
            'approved_by' => 2,
            'approved_at' => '2026-02-23 21:12:42',
            'rejected_reason' => null,
            'notes' => null,
            'created_at' => '2026-02-23 21:12:35',
            'updated_at' => '2026-02-23 21:12:42'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}