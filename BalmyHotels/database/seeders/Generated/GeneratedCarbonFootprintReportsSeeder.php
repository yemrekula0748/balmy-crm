<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : carbon_footprint_reports
 * Satır : 3
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedCarbonFootprintReportsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('carbon_footprint_reports')->truncate();
        DB::table('carbon_footprint_reports')->insert([
            [
            'id' => 2,
            'branch_id' => 1,
            'user_id' => 1,
            'title' => 'Balmy Beach Resort-Şubat Raporu',
            'report_type' => 'monthly',
            'period_start' => '2026-02-01 00:00:00',
            'period_end' => '2026-02-28 00:00:00',
            'total_guests' => 450,
            'occupied_rooms' => 675,
            'total_rooms' => 150,
            'staff_count' => 65,
            'total_area_sqm' => 5000,
            'total_co2_scope1' => 51854.69,
            'total_co2_scope2' => 66.6,
            'total_co2_scope3' => 20312.53,
            'total_co2_total' => 72233.82,
            'co2_per_guest' => 160.5196,
            'co2_per_room_night' => 107.0131,
            'co2_per_sqm' => 14.4468,
            'co2_per_staff' => 1111.2895,
            'hcmi_score' => 0,
            'hcmi_rating' => 'E',
            'renewable_energy_pct' => 100,
            'waste_recycling_rate' => 35,
            'water_intensity' => 0.1496,
            'standards_applied' => '["ISO 14064-1","GHG_Protocol","HCMI"]',
            'methodology_notes' => null,
            'improvement_notes' => null,
            'status' => 'final',
            'pdf_path' => 'carbon_reports/karbon-raporu-2-20260304.pdf',
            'finalized_at' => '2026-03-04 08:01:36',
            'created_at' => '2026-03-04 07:54:11',
            'updated_at' => '2026-03-04 08:33:28'
            ],
            [
            'id' => 3,
            'branch_id' => null,
            'user_id' => 1,
            'title' => '1232132',
            'report_type' => 'monthly',
            'period_start' => '2026-03-01 00:00:00',
            'period_end' => '2026-03-04 00:00:00',
            'total_guests' => 123,
            'occupied_rooms' => 123,
            'total_rooms' => 0,
            'staff_count' => 0,
            'total_area_sqm' => 0,
            'total_co2_scope1' => 73644.351,
            'total_co2_scope2' => 12.056,
            'total_co2_scope3' => 731.639,
            'total_co2_total' => 74388.046,
            'co2_per_guest' => 604.7809,
            'co2_per_room_night' => 604.7809,
            'co2_per_sqm' => 0,
            'co2_per_staff' => 0,
            'hcmi_score' => 0,
            'hcmi_rating' => 'E',
            'renewable_energy_pct' => 0,
            'waste_recycling_rate' => 0,
            'water_intensity' => 0.0976,
            'standards_applied' => '["ISO 14064-1","GHG_Protocol","HCMI"]',
            'methodology_notes' => null,
            'improvement_notes' => null,
            'status' => 'draft',
            'pdf_path' => 'carbon_reports/karbon-raporu-3-20260304.pdf',
            'finalized_at' => null,
            'created_at' => '2026-03-04 08:39:24',
            'updated_at' => '2026-03-04 08:39:29'
            ],
            [
            'id' => 4,
            'branch_id' => 2,
            'user_id' => 1,
            'title' => 'foresta-şubat',
            'report_type' => 'monthly',
            'period_start' => '2026-02-01 00:00:00',
            'period_end' => '2026-02-28 00:00:00',
            'total_guests' => 500,
            'occupied_rooms' => 758,
            'total_rooms' => 400,
            'staff_count' => 400,
            'total_area_sqm' => 150,
            'total_co2_scope1' => 8246342.234,
            'total_co2_scope2' => 64.9,
            'total_co2_scope3' => 20356.5,
            'total_co2_total' => 8266763.634,
            'co2_per_guest' => 16533.5273,
            'co2_per_room_night' => 10906.0206,
            'co2_per_sqm' => 55111.7576,
            'co2_per_staff' => 20666.9091,
            'hcmi_score' => 0,
            'hcmi_rating' => 'E',
            'renewable_energy_pct' => 50,
            'waste_recycling_rate' => 10,
            'water_intensity' => 0.1319,
            'standards_applied' => '["ISO 14064-1","GHG_Protocol","HCMI"]',
            'methodology_notes' => null,
            'improvement_notes' => null,
            'status' => 'final',
            'pdf_path' => 'carbon_reports/karbon-raporu-4-20260304.pdf',
            'finalized_at' => '2026-03-04 15:58:06',
            'created_at' => '2026-03-04 15:57:40',
            'updated_at' => '2026-03-04 15:58:10'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}