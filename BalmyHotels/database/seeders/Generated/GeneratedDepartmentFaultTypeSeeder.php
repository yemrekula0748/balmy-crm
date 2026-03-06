<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : department_fault_type
 * Satır : 2
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedDepartmentFaultTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('department_fault_type')->truncate();
        DB::table('department_fault_type')->insert([
            [
            'id' => 1,
            'department_id' => 12,
            'fault_type_id' => 4,
            'created_at' => null,
            'updated_at' => null
            ],
            [
            'id' => 2,
            'department_id' => 13,
            'fault_type_id' => 4,
            'created_at' => null,
            'updated_at' => null
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}