<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateSeeders extends Command
{
    protected $signature = 'db:generate-seeders
                            {--sqlite= : SQLite dosya yolu (varsayılan: .env\'deki yol)}
                            {--path=database/seeders/generated : Çıktı dizini}
                            {--chunk=200 : Her seeder dosyasında kaç satır}';

    protected $description = 'SQLite veritabanındaki verileri Laravel Seeder dosyalarına dönüştürür';

    public function handle(): int
    {
        $sqlitePath = $this->option('sqlite') ?: config('database.connections.sqlite.database');
        $outputPath = base_path($this->option('path'));
        $chunk      = (int) $this->option('chunk');

        if (!file_exists($sqlitePath)) {
            $this->error("SQLite dosyası bulunamadı: {$sqlitePath}");
            return 1;
        }

        // Çıktı dizini
        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        // SQLite bağlantısı
        config([
            'database.connections.sqlite_src' => [
                'driver'                  => 'sqlite',
                'database'                => $sqlitePath,
                'prefix'                  => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        $pdo    = DB::connection('sqlite_src')->getPdo();
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
                      ->fetchAll(\PDO::FETCH_COLUMN);

        $tables = array_values(array_filter($tables, fn($t) => $t !== 'migrations'));

        $this->info("Toplam " . count($tables) . " tablo bulundu.");
        $this->newLine();

        $seederClasses = [];
        $bar = $this->output->createProgressBar(count($tables));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        foreach ($tables as $table) {
            $bar->setMessage($table);

            $rows = DB::connection('sqlite_src')->table($table)->get()->map(fn($r) => (array)$r)->toArray();
            $className = 'Generated' . Str::studly($table) . 'Seeder';

            $this->writeSeeder($outputPath, $className, $table, $rows, $chunk);
            $seederClasses[] = $className;

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Ana DatabaseSeeder dosyasını yaz
        $this->writeMasterSeeder($outputPath, $seederClasses);

        $this->info("✓ Seeder'lar oluşturuldu → {$outputPath}");
        $this->newLine();
        $this->comment("Çalıştırmak için:");
        $this->line("  php artisan db:seed --class=GeneratedDatabaseSeeder");

        return 0;
    }

    private function writeSeeder(string $path, string $className, string $table, array $rows, int $chunk): void
    {
        $count     = count($rows);
        $rowsPhp   = '';

        if ($count > 0) {
            $chunks = array_chunk($rows, $chunk);
            $inserts = [];
            foreach ($chunks as $c) {
                $inserts[] = '        DB::table(\'' . $table . '\')->insert(' . $this->exportArray($c) . ');';
            }
            $rowsPhp = implode("\n\n", $inserts);
        } else {
            $rowsPhp = '        // Bu tabloda veri yok';
        }

        $content = <<<PHP
<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tablo : {$table}
 * Satır : {$count}
 * Otomatik üretildi: {$this->now()}
 */
class {$className} extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('{$table}')->truncate();
{$rowsPhp}
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
PHP;

        file_put_contents("{$path}/{$className}.php", $content);
    }

    private function writeMasterSeeder(string $path, array $classes): void
    {
        $calls = implode("\n", array_map(fn($c) => "        \$this->call({$c}::class);", $classes));

        $content = <<<PHP
<?php

namespace Database\Seeders\Generated;

use Illuminate\Database\Seeder;

/**
 * Otomatik üretildi: {$this->now()}
 * Çalıştır: php artisan db:seed --class=GeneratedDatabaseSeeder
 */
class GeneratedDatabaseSeeder extends Seeder
{
    public function run(): void
    {
{$calls}
    }
}
PHP;

        file_put_contents("{$path}/GeneratedDatabaseSeeder.php", $content);
    }

    private function exportArray(array $rows): string
    {
        $lines = [];
        foreach ($rows as $row) {
            $fields = [];
            foreach ($row as $key => $value) {
                $k = var_export($key, true);
                if ($value === null) {
                    $v = 'null';
                } elseif (is_int($value) || is_float($value)) {
                    $v = $value;
                } else {
                    $v = "'" . addcslashes((string)$value, "'\\") . "'";
                }
                $fields[] = "            {$k} => {$v}";
            }
            $lines[] = "            [\n" . implode(",\n", $fields) . "\n            ]";
        }
        return "[\n" . implode(",\n", $lines) . "\n        ]";
    }

    private function now(): string
    {
        return now()->format('Y-m-d H:i:s');
    }
}
