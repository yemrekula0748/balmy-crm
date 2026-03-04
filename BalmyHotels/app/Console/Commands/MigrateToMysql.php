<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateToMysql extends Command
{
    protected $signature = 'db:migrate-to-mysql
                            {--host=127.0.0.1 : MySQL sunucu adresi}
                            {--port=3306 : MySQL port}
                            {--database= : Hedef MySQL veritabanı adı}
                            {--username=root : MySQL kullanıcı adı}
                            {--password= : MySQL şifresi}
                            {--fresh : Mevcut MySQL tablolarını sıfırla (migrate:fresh)}
                            {--sqlite= : SQLite dosya yolu (varsayılan: mevcut .env\'deki yol)}';

    protected $description = 'SQLite veritabanındaki şema ve verileri MySQL\'e aktarır';

    public function handle(): int
    {
        $this->info('=== SQLite → MySQL Aktarım Aracı ===');
        $this->newLine();

        // --- 1. SQLite bağlantısını yapılandır ---
        $sqlitePath = $this->option('sqlite') ?: config('database.connections.sqlite.database');

        if (!file_exists($sqlitePath)) {
            $this->error("SQLite dosyası bulunamadı: {$sqlitePath}");
            return 1;
        }

        config([
            'database.connections.sqlite_source' => [
                'driver'                  => 'sqlite',
                'database'                => $sqlitePath,
                'prefix'                  => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        $this->line("SQLite kaynağı : {$sqlitePath}");

        // --- 2. MySQL bağlantı bilgilerini al ---
        $host     = $this->option('host');
        $port     = $this->option('port');
        $database = $this->option('database') ?: $this->ask('Hedef MySQL veritabanı adı');
        $username = $this->option('username');
        $password = $this->option('password');

        if ($password === null) {
            $password = $this->secret('MySQL şifresi (boş bırakabilirsiniz)') ?? '';
        }

        config([
            'database.connections.mysql_target' => [
                'driver'      => 'mysql',
                'host'        => $host,
                'port'        => $port,
                'database'    => $database,
                'username'    => $username,
                'password'    => $password,
                'charset'     => 'utf8mb4',
                'collation'   => 'utf8mb4_unicode_ci',
                'prefix'      => '',
                'strict'      => false,
            ],
        ]);

        // --- 3. MySQL bağlantısını test et ---
        $this->newLine();
        $this->info('MySQL bağlantısı test ediliyor...');

        try {
            DB::connection('mysql_target')->getPdo();
            $this->line("  ✓ Bağlantı başarılı → {$username}@{$host}:{$port}/{$database}");
        } catch (\Exception $e) {
            $this->error('MySQL bağlantısı başarısız: ' . $e->getMessage());
            return 1;
        }

        // --- 4. Migration'ları MySQL'e uygula ---
        $this->newLine();
        $this->info('Migration\'lar MySQL\'e uygulanıyor...');

        $migrateCommand = $this->option('fresh') ? 'migrate:fresh' : 'migrate';

        Artisan::call($migrateCommand, [
            '--database' => 'mysql_target',
            '--force'    => true,
        ]);

        $this->line(Artisan::output());
        $this->line('  ✓ Migration tamamlandı.');

        // --- 5. SQLite tablolarını listele ---
        $tables = DB::connection('sqlite_source')
            ->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");

        $tableNames = array_values(array_filter(
            array_column($tables, 'name'),
            fn ($t) => $t !== 'migrations'
        ));

        $total = count($tableNames);
        $this->newLine();
        $this->info("Veriler aktarılıyor ({$total} tablo)...");

        $bar    = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        $errors = [];

        foreach ($tableNames as $table) {
            $bar->setMessage($table);

            try {
                // Foreign key kontrolünü kapat
                DB::connection('mysql_target')->statement('SET FOREIGN_KEY_CHECKS=0');

                // SQLite'tan oku
                $rows = DB::connection('sqlite_source')
                    ->table($table)
                    ->get()
                    ->map(fn ($r) => (array) $r)
                    ->toArray();

                if (!empty($rows)) {
                    // MySQL tablosunu temizle ve yaz
                    DB::connection('mysql_target')->table($table)->truncate();

                    foreach (array_chunk($rows, 200) as $chunk) {
                        DB::connection('mysql_target')->table($table)->insert($chunk);
                    }
                }

                DB::connection('mysql_target')->statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $e) {
                $errors[$table] = $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // --- 6. Sonuç raporu ---
        if (empty($errors)) {
            $this->info('✓ Tüm veriler başarıyla MySQL\'e aktarıldı!');
        } else {
            $this->warn('Aktarım tamamlandı ancak bazı tablolarda hata oluştu:');
            foreach ($errors as $table => $error) {
                $this->error("  ✗ {$table}: {$error}");
            }
        }

        // --- 7. .env güncelleme talimatı ---
        $this->newLine();
        $this->info('Son adım — .env dosyanızı aşağıdaki değerlerle güncelleyin:');
        $this->newLine();
        $this->line("  DB_CONNECTION=mysql");
        $this->line("  DB_HOST={$host}");
        $this->line("  DB_PORT={$port}");
        $this->line("  DB_DATABASE={$database}");
        $this->line("  DB_USERNAME={$username}");
        $this->line("  DB_PASSWORD={$password}");
        $this->newLine();
        $this->comment('Bundan sonra: php artisan config:clear');

        return 0;
    }
}
