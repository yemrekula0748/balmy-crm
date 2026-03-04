<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : qr_menu_items
 * Satır : 3
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedQrMenuItemsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('qr_menu_items')->truncate();
        DB::table('qr_menu_items')->insert([
            [
            'id' => 1,
            'category_id' => 1,
            'title' => '{"tr":"3 Peynirli","en":"Three Chesee","de":"Teree \\u015e\\u0130iie"}',
            'description' => null,
            'price' => 10900,
            'image' => null,
            'is_active' => 1,
            'is_featured' => 1,
            'badges' => '["Vejeteryan","Glutensiz","\\u00d6nerilen","Yeni"]',
            'sort_order' => 1,
            'created_at' => '2026-02-23 22:16:24',
            'updated_at' => '2026-02-23 22:16:24'
            ],
            [
            'id' => 2,
            'category_id' => 2,
            'title' => '{"tr":"Adana","en":"Adana"}',
            'description' => null,
            'price' => 10000,
            'image' => 'qrmenu/items/sgmLBwXsRMK14CZEzx1WFfyl7xP3Mmt9wElNw7Xv.jpg',
            'is_active' => 1,
            'is_featured' => 1,
            'badges' => null,
            'sort_order' => 0,
            'created_at' => '2026-02-23 22:56:52',
            'updated_at' => '2026-02-23 22:56:52'
            ],
            [
            'id' => 3,
            'category_id' => 3,
            'title' => '{"tr":"3 Peynirli","en":"Turkish Food"}',
            'description' => null,
            'price' => 19210,
            'image' => 'qrmenu/items/adjRuRWczEQolM1XFlxnNlbrjZNbdynADrKKcM62.jpg',
            'is_active' => 1,
            'is_featured' => 0,
            'badges' => '["Vejeteryan","Glutensiz","Ac\\u0131l\\u0131"]',
            'sort_order' => 0,
            'created_at' => '2026-02-23 23:34:59',
            'updated_at' => '2026-02-23 23:34:59'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}