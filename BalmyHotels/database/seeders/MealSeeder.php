<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MealSeeder extends Seeder
{
    /**
     * Eski projeden gelen meals.sql → food_labels tablosuna aktarır.
     * Migration değişmez; sadece veri insert edilir.
     */

    // ---------------------------------------------------------------------------
    // Eski SQL'deki allerjen string'lerini FoodLabel::ALLERGENS key'lerine çevirir
    // Tanımlanmamış / gürültülü değerler null döner (atlanır)
    // ---------------------------------------------------------------------------
    private const ALLERGEN_MAP = [
        'GLUTEN'                  => 'gluten',
        'SULFIT'                  => 'sulphites',
        'SUT URUNLERI'            => 'milk',
        'SUT'                     => 'milk',
        'FINDIK'                  => 'nuts',
        'SERT KABUKLU MEYVELER'   => 'nuts',
        'WALNUSS'                 => 'nuts',
        'WALNUT'                  => 'nuts',
        'YUMURTA'                 => 'eggs',
        'BALIK'                   => 'fish',
        'FISCH'                   => 'fish',
        'FISH'                    => 'fish',
        'SUSAM'                   => 'sesame',
        'KABUKLULAR'              => 'crustaceans',
        'KABUKLU DENIZ URUNLERI'  => 'crustaceans',
        'HARDAL'                  => 'mustard',
        'KEREVIZ'                 => 'celery',
        'SELLERIE'                => 'celery',
        'ACI BAKLA'               => 'lupin',
        'SOYA'                    => 'soybeans',
        'YERFISTIGI'              => 'peanuts',
        'YER FISTIGI'             => 'peanuts',
    ];

    // ---------------------------------------------------------------------------
    // name_tr'den kategori tahmin eder
    // ---------------------------------------------------------------------------
    private function detectCategory(string $nameTr): string
    {
        $name = mb_strtolower($nameTr, 'UTF-8');

        if (str_contains($name, 'çorba') || str_contains($name, 'corba')) {
            return 'soup';
        }
        if (str_contains($name, 'salata') || str_contains($name, 'salat')) {
            return 'salad';
        }
        if (
            str_contains($name, 'tatlı')    || str_contains($name, 'tatli')   ||
            str_contains($name, 'dondurma') || str_contains($name, 'pasta')   ||
            str_contains($name, 'kek')      || str_contains($name, 'baklava') ||
            str_contains($name, 'komposto') || str_contains($name, 'marmelad') ||
            str_contains($name, 'puding')   || str_contains($name, 'tart')    ||
            str_contains($name, 'reçel')    || str_contains($name, 'recel')
        ) {
            return 'dessert';
        }
        if (
            str_contains($name, 'çay')      || str_contains($name, 'kahve')   ||
            str_contains($name, 'limonata') || str_contains($name, 'milkshak') ||
            str_contains($name, 'meyve suyu') || str_contains($name, 'juice') ||
            str_contains($name, 'smoothie') || str_contains($name, 'tea')     ||
            str_contains($name, 'koffee')
        ) {
            return 'beverage';
        }
        if (
            str_contains($name, 'kahvaltı') || str_contains($name, 'kahvalti') ||
            str_contains($name, 'omlet')    || str_contains($name, 'omlet')
        ) {
            return 'breakfast';
        }
        if (
            str_contains($name, 'pilav')   || str_contains($name, 'makarna')  ||
            str_contains($name, 'kebap')   || str_contains($name, 'kebabı')   ||
            str_contains($name, 'köfte')   || str_contains($name, ' eti')     ||
            str_contains($name, 'güveç')   || str_contains($name, 'rosto')    ||
            str_contains($name, 'ızgara')  || str_contains($name, 'izgara')   ||
            str_contains($name, 'kavurma') || str_contains($name, 'sote')     ||
            str_contains($name, 'dolma')   || str_contains($name, 'sarma')    ||
            str_contains($name, 'fırın')   || str_contains($name, 'firin')
        ) {
            return 'main';
        }
        if (
            str_contains($name, 'ekmek')  || str_contains($name, 'bread')    ||
            str_contains($name, 'kruton') || str_contains($name, 'kruvasan') ||
            str_contains($name, 'bazlama')
        ) {
            return 'side';
        }

        return 'main';
    }

    // ---------------------------------------------------------------------------
    // Bir SQL değer tuple'ını dizi olarak parse eder
    // Örn: (1, 'Ad', 'Name', NULL, 300, ...) → ['1', 'Ad', 'Name', null, '300', ...]
    // ---------------------------------------------------------------------------
    private function parseTuple(string $line): ?array
    {
        $line = trim($line);

        // Boş satır veya ( ile başlamıyorsa atla
        if ($line === '' || $line[0] !== '(') {
            return null;
        }

        // Sona eklenen virgül / noktalı virgülü temizle
        $line = rtrim($line, ',;');
        // Dıştaki parantezleri kaldır
        $line = substr($line, 1, -1);

        $values  = [];
        $len     = strlen($line);
        $i       = 0;
        $current = '';
        $inQuote = false;

        while ($i < $len) {
            $ch = $line[$i];

            if ($inQuote) {
                if ($ch === "\\") {
                    // Backslash escape
                    $i++;
                    if ($i < $len) {
                        $current .= $line[$i];
                    }
                } elseif ($ch === "'") {
                    // MySQL doubled-quote escape: ''
                    if ($i + 1 < $len && $line[$i + 1] === "'") {
                        $current .= "'";
                        $i += 2;
                        continue;
                    }
                    // Kapanan tırnak
                    $inQuote = false;
                    $values[] = $current;
                    $current  = '';
                    // Virgüle kadar git
                    while ($i + 1 < $len && $line[$i + 1] !== ',') {
                        $i++;
                    }
                    $i += 2; // virgülü de geç
                    continue;
                } else {
                    $current .= $ch;
                }
            } else {
                if ($ch === "'") {
                    $inQuote = true;
                } elseif ($ch === ',') {
                    $trimmed  = trim($current);
                    $values[] = ($trimmed === 'NULL') ? null : $trimmed;
                    $current  = '';
                } else {
                    $current .= $ch;
                }
            }

            $i++;
        }

        // Son değer
        $trimmed = trim($current);
        if ($trimmed !== '') {
            $values[] = ($trimmed === 'NULL') ? null : $trimmed;
        }

        return count($values) >= 19 ? $values : null;
    }

    // ---------------------------------------------------------------------------
    // Allerjen string dizisini FoodLabel key dizisine çevirir
    // ---------------------------------------------------------------------------
    private function mapAllergens(array $rawAllergens): array
    {
        $result = [];
        foreach ($rawAllergens as $raw) {
            if ($raw === null || $raw === '' || $raw === 'Atanmamış') {
                continue;
            }
            $key = self::ALLERGEN_MAP[strtoupper(trim($raw))] ?? null;
            if ($key && !in_array($key, $result, true)) {
                $result[] = $key;
            }
        }
        return $result;
    }

    // ---------------------------------------------------------------------------
    // Ana çalıştırma
    // ---------------------------------------------------------------------------
    public function run(): void
    {
        $sqlPath = database_path('meals.sql');

        if (!file_exists($sqlPath)) {
            $this->command->error('meals.sql bulunamadı: ' . $sqlPath);
            return;
        }

        $handle = fopen($sqlPath, 'r');
        if (!$handle) {
            $this->command->error('meals.sql açılamadı.');
            return;
        }

        $now       = now()->toDateTimeString();
        $batch     = [];
        $total     = 0;
        $skipped   = 0;
        $inValues  = false;
        $sortOrder = 0;

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");

            // VALUES başlangıcını yakala
            if (!$inValues) {
                if (str_starts_with($line, 'INSERT INTO `meals`')) {
                    $inValues = true;
                }
                continue;
            }

            // VALUES bloğunun sonu
            if (str_starts_with($line, 'ALTER') || str_starts_with($line, '--') && $total > 0) {
                break;
            }

            // Tuple satırı
            $values = $this->parseTuple($line);
            if (!$values) {
                continue;
            }

            // Sütun sırası:
            // 0:id  1:name_tr  2:name_en  3:name_de  4:name_ru
            // 5:calories  6:fat  7:protein  8:carbs
            // 9..18: allergen1..allergen10
            // 19:created_at  20:updated_at

            $nameTr = trim($values[1] ?? '');
            $nameEn = trim($values[2] ?? '');
            $nameDe = trim($values[3] ?? '');
            $nameRu = trim($values[4] ?? '');

            if ($nameTr === '' && $nameEn === '') {
                $skipped++;
                continue;
            }

            $calories = isset($values[5]) && $values[5] !== null && $values[5] !== '0'
                ? (int) $values[5]
                : null;

            $rawAllergens = array_slice($values, 9, 10);
            $allergens    = $this->mapAllergens($rawAllergens);

            $sortOrder++;

            $batch[] = [
                'branch_id'     => null,
                'created_by'    => null,
                'name'          => json_encode(array_filter([
                    'tr' => $nameTr ?: null,
                    'en' => $nameEn ?: null,
                    'de' => $nameDe ?: null,
                    'ru' => $nameRu ?: null,
                ]), JSON_UNESCAPED_UNICODE),
                'description'   => null,
                'ingredients'   => null,
                'calories'      => $calories,
                'allergens'     => empty($allergens) ? null : json_encode($allergens, JSON_UNESCAPED_UNICODE),
                'category'      => $this->detectCategory($nameTr ?: $nameEn),
                'is_vegan'      => 0,
                'is_vegetarian' => 0,
                'is_halal'      => 0,
                'is_active'     => 1,
                'sort_order'    => $sortOrder,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            $total++;

            // Her 500 kayıtta bir insert et
            if (count($batch) >= 500) {
                DB::table('food_labels')->insert($batch);
                $batch = [];
                $this->command->info("  ↳ {$total} kayıt aktarıldı...");
            }
        }

        fclose($handle);

        // Kalan kayıtlar
        if (!empty($batch)) {
            DB::table('food_labels')->insert($batch);
        }

        $this->command->info("✓ Toplam {$total} yemek food_labels tablosuna aktarıldı. ({$skipped} satır atlandı)");
    }
}
