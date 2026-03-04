<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : staff_survey_answers
 * Satır : 3
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedStaffSurveyAnswersSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('staff_survey_answers')->truncate();
        DB::table('staff_survey_answers')->insert([
            [
            'id' => 1,
            'response_id' => 1,
            'question_id' => 1,
            'answer' => 'Evet',
            'created_at' => '2026-02-24 19:39:11',
            'updated_at' => '2026-02-24 19:39:11'
            ],
            [
            'id' => 2,
            'response_id' => 1,
            'question_id' => 2,
            'answer' => 'iyi',
            'created_at' => '2026-02-24 19:39:11',
            'updated_at' => '2026-02-24 19:39:11'
            ],
            [
            'id' => 3,
            'response_id' => 1,
            'question_id' => 3,
            'answer' => '3',
            'created_at' => '2026-02-24 19:39:11',
            'updated_at' => '2026-02-24 19:39:11'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}