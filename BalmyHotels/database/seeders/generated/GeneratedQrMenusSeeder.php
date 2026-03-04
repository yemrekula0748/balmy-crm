<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : qr_menus
 * Satır : 2
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedQrMenusSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('qr_menus')->truncate();
        DB::table('qr_menus')->insert([
            [
            'id' => 2,
            'branch_id' => 1,
            'created_by' => 2,
            'name' => 'balmy-restorans',
            'title' => '{"tr":null,"en":"\\u0130talyano Sofrano Oturmayano","de":"Itaolsd Sf\\u0131sfur Otudtturum"}',
            'description' => null,
            'logo' => 'qrmenu/logos/IaMrMAV5mc8dbq24jK1jb4XU28gAJBWcyA9KChjb.svg',
            'cover_image' => null,
            'theme_color' => '#c19b77',
            'is_active' => 1,
            'currency' => 'TRY',
            'currency_symbol' => '₺',
            'created_at' => '2026-02-23 22:13:08',
            'updated_at' => '2026-02-23 22:13:08'
            ],
            [
            'id' => 3,
            'branch_id' => 1,
            'created_by' => 2,
            'name' => 'test',
            'title' => '{"tr":null,"en":"Test Men\\u00fc Eng"}',
            'description' => null,
            'logo' => 'qrmenu/logos/7vSvD45afkG34qONwR4QMHePjwjE7RsQmx5o6njO.png',
            'cover_image' => 'qrmenu/covers/8akKs2ElgY0wKStIlGtLEfcu4Xnm7XL1EwRle9YC.jpg',
            'theme_color' => '#c19b77',
            'is_active' => 1,
            'currency' => 'TRY',
            'currency_symbol' => '₺',
            'created_at' => '2026-02-23 22:53:33',
            'updated_at' => '2026-02-23 22:53:33'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}