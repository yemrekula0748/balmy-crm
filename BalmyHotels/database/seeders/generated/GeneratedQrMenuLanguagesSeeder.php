<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : qr_menu_languages
 * Satır : 5
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedQrMenuLanguagesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('qr_menu_languages')->truncate();
        DB::table('qr_menu_languages')->insert([
            [
            'id' => 2,
            'qr_menu_id' => 2,
            'code' => 'tr',
            'name' => 'Türkçe',
            'flag' => '🇹🇷',
            'is_default' => 1,
            'sort_order' => 0,
            'created_at' => '2026-02-23 22:13:08',
            'updated_at' => '2026-02-23 22:13:08'
            ],
            [
            'id' => 3,
            'qr_menu_id' => 2,
            'code' => 'en',
            'name' => 'English',
            'flag' => '🇬🇧',
            'is_default' => 0,
            'sort_order' => 1,
            'created_at' => '2026-02-23 22:13:08',
            'updated_at' => '2026-02-23 22:13:08'
            ],
            [
            'id' => 4,
            'qr_menu_id' => 2,
            'code' => 'de',
            'name' => 'Deutsch',
            'flag' => '🇩🇪',
            'is_default' => 0,
            'sort_order' => 2,
            'created_at' => '2026-02-23 22:13:08',
            'updated_at' => '2026-02-23 22:13:08'
            ],
            [
            'id' => 5,
            'qr_menu_id' => 3,
            'code' => 'tr',
            'name' => 'Türkçe',
            'flag' => '🇹🇷',
            'is_default' => 1,
            'sort_order' => 0,
            'created_at' => '2026-02-23 22:53:33',
            'updated_at' => '2026-02-23 22:53:33'
            ],
            [
            'id' => 6,
            'qr_menu_id' => 3,
            'code' => 'en',
            'name' => 'English',
            'flag' => '🇬🇧',
            'is_default' => 0,
            'sort_order' => 1,
            'created_at' => '2026-02-23 22:53:33',
            'updated_at' => '2026-02-23 22:53:33'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}