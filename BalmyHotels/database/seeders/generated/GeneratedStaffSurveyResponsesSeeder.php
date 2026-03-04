<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : staff_survey_responses
 * Satır : 1
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedStaffSurveyResponsesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('staff_survey_responses')->truncate();
        DB::table('staff_survey_responses')->insert([
            [
            'id' => 1,
            'survey_id' => 1,
            'respondent_name' => null,
            'respondent_dept' => 'Bilgi İşlem',
            'respondent_employee_id' => null,
            'lang' => 'tr',
            'respondent_token' => 'gKcd6KjQL0pOyLFNWFYzVvDzuc2CmA9Uuzlk9gew',
            'ip_address' => '127.0.0.1',
            'submitted_at' => '2026-02-24 19:39:11',
            'created_at' => '2026-02-24 19:39:11',
            'updated_at' => '2026-02-24 19:39:11'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}