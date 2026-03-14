<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : qr_menu_categories
 * Satır : 3
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedQrMenuCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('qr_menu_categories')->truncate();
        DB::table('qr_menu_categories')->insert([
            [
            'id' => 1,
            'qr_menu_id' => 2,
            'title' => '{"tr":"Pizza","en":"Pizza","de":"Phizzaa"}',
            'description' => null,
            'icon' => null,
            'image' => null,
            'sort_order' => 0,
            'is_active' => 1,
            'created_at' => '2026-02-23 22:15:01',
            'updated_at' => '2026-02-23 22:15:01'
            ],
            [
            'id' => 2,
            'qr_menu_id' => 3,
            'title' => '{"tr":"T\\u00fcrk Yemekleri","en":"Turkish Food"}',
            'description' => null,
            'icon' => null,
            'image' => 'qrmenu/categories/zw3RWxCeluVD3z65Qdtkg8KxYbnfR0fol6m54uQ1.jpg',
            'sort_order' => 0,
            'is_active' => 1,
            'created_at' => '2026-02-23 22:54:36',
            'updated_at' => '2026-02-23 22:54:36'
            ],
            [
            'id' => 3,
            'qr_menu_id' => 3,
            'title' => '{"tr":"\\u0130talyan Yemekleri","en":"\\u0130taliano Food"}',
            'description' => null,
            'icon' => null,
            'image' => 'qrmenu/categories/YedfIe1d3qPuRNYxMf4xIXuGDAyRz2648lOZYLXG.jpg',
            'sort_order' => 0,
            'is_active' => 1,
            'created_at' => '2026-02-23 22:55:48',
            'updated_at' => '2026-02-23 22:55:48'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}