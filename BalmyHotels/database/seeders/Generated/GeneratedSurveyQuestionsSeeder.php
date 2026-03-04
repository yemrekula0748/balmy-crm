<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : survey_questions
 * Satır : 5
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedSurveyQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('survey_questions')->truncate();
        DB::table('survey_questions')->insert([
            [
            'id' => 6,
            'survey_id' => 1,
            'sort_order' => 1,
            'type' => 'checkbox',
            'is_required' => 1,
            'translations' => '{"tr":{"text":"\\u00c7oklu Se\\u00e7im","options":["1","2","3","4"]},"en":{"text":"\\u00c7oklu Se\\u00e7im EN","options":[""]}}',
            'conditional_question_id' => null,
            'conditional_answer_value' => null,
            'created_at' => '2026-02-24 18:37:33',
            'updated_at' => '2026-02-24 18:37:33'
            ],
            [
            'id' => 7,
            'survey_id' => 1,
            'sort_order' => 2,
            'type' => 'rating',
            'is_required' => 1,
            'translations' => '{"tr":{"text":"Y\\u0131ld\\u0131z De\\u011ferlendirme","options":[]},"en":{"text":"Y\\u0131ld\\u0131z De\\u011ferlendirme EN","options":[]}}',
            'conditional_question_id' => null,
            'conditional_answer_value' => null,
            'created_at' => '2026-02-24 18:37:33',
            'updated_at' => '2026-02-24 18:37:33'
            ],
            [
            'id' => 8,
            'survey_id' => 1,
            'sort_order' => 3,
            'type' => 'nps',
            'is_required' => 1,
            'translations' => '{"tr":{"text":"NPS Skor De\\u011ferlendirme","options":[]},"en":{"text":"NPS Skor De\\u011ferlendirme EN","options":[]}}',
            'conditional_question_id' => null,
            'conditional_answer_value' => null,
            'created_at' => '2026-02-24 18:37:33',
            'updated_at' => '2026-02-24 18:37:33'
            ],
            [
            'id' => 9,
            'survey_id' => 1,
            'sort_order' => 4,
            'type' => 'text',
            'is_required' => 1,
            'translations' => '{"tr":{"text":"K\\u0131sa Metin De\\u011ferlendirme","options":[]},"en":{"text":"K\\u0131sa Metin De\\u011ferlendirme","options":[]}}',
            'conditional_question_id' => null,
            'conditional_answer_value' => null,
            'created_at' => '2026-02-24 18:37:33',
            'updated_at' => '2026-02-24 18:37:33'
            ],
            [
            'id' => 10,
            'survey_id' => 1,
            'sort_order' => 5,
            'type' => 'textarea',
            'is_required' => 1,
            'translations' => '{"tr":{"text":"Uzun Metin De\\u011ferlendirme","options":[]},"en":{"text":"Uzun Metin De\\u011ferlendirme EN","options":[]}}',
            'conditional_question_id' => null,
            'conditional_answer_value' => null,
            'created_at' => '2026-02-24 18:37:33',
            'updated_at' => '2026-02-24 18:37:33'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}