<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : survey_answers
 * Satır : 5
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedSurveyAnswersSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('survey_answers')->truncate();
        DB::table('survey_answers')->insert([
            [
            'id' => 6,
            'response_id' => 2,
            'question_id' => 6,
            'answer' => '[null]',
            'created_at' => '2026-02-24 18:38:15',
            'updated_at' => '2026-02-24 18:38:15'
            ],
            [
            'id' => 7,
            'response_id' => 2,
            'question_id' => 7,
            'answer' => '1',
            'created_at' => '2026-02-24 18:38:15',
            'updated_at' => '2026-02-24 18:38:15'
            ],
            [
            'id' => 8,
            'response_id' => 2,
            'question_id' => 8,
            'answer' => '2',
            'created_at' => '2026-02-24 18:38:15',
            'updated_at' => '2026-02-24 18:38:15'
            ],
            [
            'id' => 9,
            'response_id' => 2,
            'question_id' => 9,
            'answer' => 'Sadi Test 2',
            'created_at' => '2026-02-24 18:38:15',
            'updated_at' => '2026-02-24 18:38:15'
            ],
            [
            'id' => 10,
            'response_id' => 2,
            'question_id' => 10,
            'answer' => 'asdadasdad',
            'created_at' => '2026-02-24 18:38:15',
            'updated_at' => '2026-02-24 18:38:15'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}