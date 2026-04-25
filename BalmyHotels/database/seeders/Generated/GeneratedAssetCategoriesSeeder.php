<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : asset_categories
 * Satır : 3
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedAssetCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('asset_categories')->truncate();
        DB::table('asset_categories')->insert([
            [
            'id' => 1,
            'name' => 'Elektronik',
            'color' => '#15d1ab',
            'description' => 'Bilgi İşlem Ürünleri',
            'field_definitions' => '[{"name":"001","label":"Ruijie","type":"number","required":false,"options":[]}]',
            'created_at' => '2026-02-23 21:10:56',
            'updated_at' => '2026-02-23 21:10:56'
            ],
            [
            'id' => 2,
            'name' => 'Mobilt',
            'color' => '#c19b77',
            'description' => null,
            'field_definitions' => null,
            'created_at' => '2026-02-24 07:49:30',
            'updated_at' => '2026-02-24 07:49:30'
            ],
            [
            'id' => 3,
            'name' => 'Mobilya',
            'color' => '#e66f00',
            'description' => null,
            'field_definitions' => '[{"name":"001","label":"Ruijie","type":"select","required":true,"options":["Siyah","Beyaz","Gri"]}]',
            'created_at' => '2026-02-24 07:49:59',
            'updated_at' => '2026-02-24 07:49:59'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}