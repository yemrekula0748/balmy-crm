<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : branches
 * Satır : 2
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedBranchesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('branches')->truncate();
        DB::table('branches')->insert([
            [
            'id' => 1,
            'name' => 'Balmy Beach Resort',
            'slug' => 'balmy-beach-resort',
            'address' => null,
            'phone' => null,
            'is_active' => 1,
            'created_at' => '2026-02-23 20:23:13',
            'updated_at' => '2026-02-23 20:23:13'
            ],
            [
            'id' => 2,
            'name' => 'Balmy Foresta',
            'slug' => 'balmy-foresta',
            'address' => null,
            'phone' => null,
            'is_active' => 1,
            'created_at' => '2026-02-23 20:23:13',
            'updated_at' => '2026-02-23 20:23:13'
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}