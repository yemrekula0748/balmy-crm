<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : surveys
 * Satır : 1
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedSurveysSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('surveys')->truncate();
        DB::table('surveys')->insert([
            [
            'id' => 1,
            'branch_id' => null,
            'created_by' => 1,
            'title' => '{"tr":"Personel 360 De\\u011ferlendirme","en":"Person 360"}',
            'description' => '{"tr":null,"en":null}',
            'slug' => 'personel-360-degerlendirme-KhfVrg',
            'languages' => '["tr","en"]',
            'is_active' => 1,
            'show_language_select' => 1,
            'created_at' => '2026-02-24 18:24:37',
            'updated_at' => '2026-02-24 18:27:22'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}