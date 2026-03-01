<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SQLite ALTER COLUMN desteklemediğinden tabloyu yeniden oluşturuyoruz.
     * branch_id → nullable yapılıyor (şubesi olmayan kullanıcılar için).
     */
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE IF NOT EXISTS door_logs_new (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                branch_id  INTEGER NULL     REFERENCES branches(id) ON DELETE SET NULL,
                type       VARCHAR CHECK(type IN (\'giris\',\'cikis\')),
                logged_at  DATETIME NOT NULL,
                notes      VARCHAR NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ');

        DB::statement('INSERT INTO door_logs_new SELECT * FROM door_logs');
        DB::statement('DROP TABLE door_logs');
        DB::statement('ALTER TABLE door_logs_new RENAME TO door_logs');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // branch_id'yi tekrar NOT NULL yapmak için aynı süreç
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE IF NOT EXISTS door_logs_old (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                branch_id  INTEGER NOT NULL REFERENCES branches(id) ON DELETE CASCADE,
                type       VARCHAR CHECK(type IN (\'giris\',\'cikis\')),
                logged_at  DATETIME NOT NULL,
                notes      VARCHAR NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ');

        DB::statement('INSERT INTO door_logs_old SELECT * FROM door_logs WHERE branch_id IS NOT NULL');
        DB::statement('DROP TABLE door_logs');
        DB::statement('ALTER TABLE door_logs_old RENAME TO door_logs');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
