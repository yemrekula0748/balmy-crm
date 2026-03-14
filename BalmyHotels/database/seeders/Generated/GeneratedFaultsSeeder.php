<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : faults
 * Satır : 2
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedFaultsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('faults')->truncate();
        DB::table('faults')->insert([
            [
            'id' => 3,
            'branch_id' => 2,
            'reported_by' => 4,
            'assigned_department_id' => 12,
            'assigned_to' => null,
            'title' => 'İnternet Arızası',
            'description' => 'modem kopmuş',
            'location' => null,
            'priority' => 'medium',
            'status' => 'closed',
            'resolved_at' => '2026-03-02 17:08:57',
            'closed_at' => '2026-03-02 17:08:57',
            'created_at' => '2026-03-02 17:01:23',
            'updated_at' => '2026-03-02 17:08:57',
            'fault_type_id' => 4,
            'fault_location_id' => 2,
            'fault_area_id' => 2,
            'image_path' => null
            ],
            [
            'id' => 4,
            'branch_id' => 2,
            'reported_by' => 4,
            'assigned_department_id' => 12,
            'assigned_to' => null,
            'title' => 'İnternet Arızası',
            'description' => 'adasdasdsadsadas',
            'location' => null,
            'priority' => 'medium',
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
            'created_at' => '2026-03-02 17:13:47',
            'updated_at' => '2026-03-02 17:13:47',
            'fault_type_id' => 4,
            'fault_location_id' => 2,
            'fault_area_id' => 4,
            'image_path' => null
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}