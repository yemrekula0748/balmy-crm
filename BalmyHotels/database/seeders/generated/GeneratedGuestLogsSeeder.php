<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : guest_logs
 * Satır : 1
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedGuestLogsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('guest_logs')->truncate();
        DB::table('guest_logs')->insert([
            [
            'id' => 1,
            'branch_id' => 1,
            'department_id' => 1,
            'host_user_id' => 2,
            'created_by' => 1,
            'visitor_name' => 'Oktay KARA',
            'visitor_phone' => null,
            'visitor_id_no' => '1258585563',
            'visitor_company' => 'DSI',
            'purpose' => 'other',
            'purpose_note' => 'Muhabbet',
            'check_in_at' => '2026-02-24 12:48:00',
            'check_out_at' => '2026-03-01 21:02:00',
            'notes' => null,
            'created_at' => '2026-02-24 12:48:56',
            'updated_at' => '2026-03-01 21:02:00'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}