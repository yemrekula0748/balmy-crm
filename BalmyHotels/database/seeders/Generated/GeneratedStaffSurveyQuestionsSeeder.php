<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : staff_survey_questions
 * Satır : 3
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedStaffSurveyQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('staff_survey_questions')->truncate();
        DB::table('staff_survey_questions')->insert([
            [
            'id' => 1,
            'survey_id' => 1,
            'sort_order' => 0,
            'type' => 'yesno',
            'title' => '{"tr":"Personel giri\\u015fte pasaport fotokopisini \\u00e7ekti mi ? "}',
            'options' => null,
            'required' => 1,
            'condition_question_id' => null,
            'condition_answer' => null,
            'created_at' => '2026-02-24 19:38:32',
            'updated_at' => '2026-02-24 19:38:32'
            ],
            [
            'id' => 2,
            'survey_id' => 1,
            'sort_order' => 1,
            'type' => 'radio',
            'title' => '{"tr":"Nas\\u0131l"}',
            'options' => '{"tr":["iyi"]}',
            'required' => 1,
            'condition_question_id' => null,
            'condition_answer' => null,
            'created_at' => '2026-02-24 19:38:32',
            'updated_at' => '2026-02-24 19:38:32'
            ],
            [
            'id' => 3,
            'survey_id' => 1,
            'sort_order' => 2,
            'type' => 'rating',
            'title' => '{"tr":"Hayat Nas\\u0131l "}',
            'options' => null,
            'required' => 1,
            'condition_question_id' => null,
            'condition_answer' => null,
            'created_at' => '2026-02-24 19:38:32',
            'updated_at' => '2026-02-24 19:38:32'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}