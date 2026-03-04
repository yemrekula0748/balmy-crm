<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : users
 * Satır : 6
 * Otomatik üretildi: 2026-03-04 17:54:07
 */
class GeneratedUsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('users')->insert([
            [
            'id' => 1,
            'name' => 'YunusEmre',
            'email' => 'admin@balmy.com',
            'email_verified_at' => null,
            'password' => '$2y$10$OC0I1xtotSg1KXG/6m9heeUmrJbp9cLtDWLjLGYc05XW4.V3paEjS',
            'remember_token' => null,
            'created_at' => '2026-02-23 20:23:13',
            'updated_at' => '2026-02-23 20:23:13',
            'branch_id' => null,
            'department_id' => null,
            'role' => 'super_admin',
            'phone' => null,
            'title' => null,
            'is_active' => 1
            ],
            [
            'id' => 2,
            'name' => 'Sadi SEVINC',
            'email' => 'sadi@sevilsmile.com',
            'email_verified_at' => null,
            'password' => '$2y$10$UXskYgktzzXIo39oPvL6/u.N8W3t0dMwiGsq807C484MopBR5l2ne',
            'remember_token' => null,
            'created_at' => '2026-02-23 20:26:02',
            'updated_at' => '2026-02-23 20:47:17',
            'branch_id' => 1,
            'department_id' => 1,
            'role' => 'super_admin',
            'phone' => '541 494 5003',
            'title' => 'CRM Manager',
            'is_active' => 1
            ],
            [
            'id' => 3,
            'name' => 'Teknik Personel Ahmet',
            'email' => 'teknik@balmy.com',
            'email_verified_at' => null,
            'password' => '$2y$10$4V7rekrTm6NqFnKyRhsEfusW2RToDB1MWBceGiKFR4KRre/OzuQTC',
            'remember_token' => null,
            'created_at' => '2026-02-27 18:36:04',
            'updated_at' => '2026-02-27 18:36:04',
            'branch_id' => 1,
            'department_id' => 1,
            'role' => 'teknik_ariza_personeli',
            'phone' => null,
            'title' => 'Teknik Personel',
            'is_active' => 1
            ],
            [
            'id' => 4,
            'name' => 'Ana Kapı Personeli',
            'email' => 'maingate@balmyhotels.com',
            'email_verified_at' => null,
            'password' => '$2y$10$wJB95gX/7RmQLDfwByk2c.shXl8As1HiVNJyvAzP5tKg/fmBGfX.G',
            'remember_token' => null,
            'created_at' => '2026-03-02 12:53:14',
            'updated_at' => '2026-03-02 12:53:14',
            'branch_id' => 2,
            'department_id' => 3,
            'role' => 'anakapi',
            'phone' => '055544444444',
            'title' => 'Ana Kapı Personeli',
            'is_active' => 1
            ],
            [
            'id' => 5,
            'name' => 'Ahmet KARAYAVAŞ',
            'email' => 'animation@balmyhotels.com',
            'email_verified_at' => null,
            'password' => '$2y$10$KzZNGvgn98mj7bZ7H0/eIeWEfcRR3oWAy4xl6H83UgLP62NMOZSiW',
            'remember_token' => null,
            'created_at' => '2026-03-02 15:43:56',
            'updated_at' => '2026-03-02 15:43:56',
            'branch_id' => 2,
            'department_id' => 11,
            'role' => 'dept_manager',
            'phone' => '05441111111',
            'title' => 'Animasyon Müdürü',
            'is_active' => 1
            ],
            [
            'id' => 6,
            'name' => 'Mertcan BALCI',
            'email' => 'it2@balmyhotels.com',
            'email_verified_at' => null,
            'password' => '$2y$10$DqwXM1NpiWcJWTVOQ3PgOuZCWKAa4oxnVcsLZgqc5ze8SoIh6erey',
            'remember_token' => null,
            'created_at' => '2026-03-02 17:06:32',
            'updated_at' => '2026-03-02 17:06:32',
            'branch_id' => 2,
            'department_id' => 12,
            'role' => 'teknik_ariza_personeli',
            'phone' => '05389321203',
            'title' => 'Grup Bilgi İşlem Elemanı',
            'is_active' => 1
            ]
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}