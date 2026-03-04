<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : staff_surveys
 * Satır : 1
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedStaffSurveysSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('staff_surveys')->truncate();
        DB::table('staff_surveys')->insert([
            [
            'id' => 1,
            'branch_id' => null,
            'created_by' => 1,
            'title' => '{"tr":"Otel Personeli De\\u011ferlendirme"}',
            'description' => '{"tr":null}',
            'slug' => 'otel-personeli-degerlendirme-hsxEln',
            'languages' => '["tr"]',
            'is_anonymous' => 1,
            'show_dept_field' => 1,
            'show_employee_id_field' => 0,
            'show_language_select' => 1,
            'allow_multiple' => 0,
            'is_active' => 1,
            'created_at' => '2026-02-24 19:37:16',
            'updated_at' => '2026-02-24 19:37:16'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}