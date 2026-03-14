<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : survey_responses
 * Satır : 2
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedSurveyResponsesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('survey_responses')->truncate();
        DB::table('survey_responses')->insert([
            [
            'id' => 1,
            'survey_id' => 1,
            'lang' => 'tr',
            'respondent_token' => 'CUMbUhMqJbrIBtcVl5iQeVMNdsg6VeXewAJe1DUL',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
            'submitted_at' => '2026-02-24 18:25:19',
            'created_at' => '2026-02-24 18:25:19',
            'updated_at' => '2026-02-24 18:25:19'
            ],
            [
            'id' => 2,
            'survey_id' => 1,
            'lang' => 'en',
            'respondent_token' => 'DqMgXz3FhU84miE03M7lvwYUVRRPAVjibUjgnuIG',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
            'submitted_at' => '2026-02-24 18:38:15',
            'created_at' => '2026-02-24 18:38:15',
            'updated_at' => '2026-02-24 18:38:15'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}