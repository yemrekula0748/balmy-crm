<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : personal_access_tokens
 * Satır : 0
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedPersonalAccessTokensSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('personal_access_tokens')->truncate();
        // Bu tabloda veri yok
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}