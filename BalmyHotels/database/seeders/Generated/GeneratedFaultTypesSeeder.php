<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : fault_types
 * Satır : 1
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedFaultTypesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('fault_types')->truncate();
        DB::table('fault_types')->insert([
            [
            'id' => 4,
            'branch_id' => null,
            'name' => 'İnternet Arızası',
            'completion_hours' => 2,
            'is_active' => 1,
            'created_at' => '2026-03-02 16:58:58',
            'updated_at' => '2026-03-02 16:58:58'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}