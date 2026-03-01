<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Yemek isimlik import seeder.
 * Kullanim: php artisan db:seed --class=FoodLabelImportSeeder
 * Kaynak:   database/yemek_isimlikler_dolu.xlsx
 */
class FoodLabelImportSeeder extends Seeder
{
    public function run(): void
    {
        $xlsxPath = database_path('yemek_isimlikler_dolu.xlsx');

        if (!file_exists($xlsxPath)) {
            $this->command->error("Dosya bulunamadi: $xlsxPath");
            return;
        }

        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ym_seed_' . time();
        $tempZip = $tempDir . '.zip';
        copy($xlsxPath, $tempZip);

        exec('powershell -NoProfile -Command "Expand-Archive -Path \'' . $tempZip . '\' -DestinationPath \'' . $tempDir . '\' -Force"', $o, $rc);
        @unlink($tempZip);

        if ($rc !== 0 || !is_dir($tempDir)) {
            $this->command->error('Extract basarisiz.');
            return;
        }

        $strings = [];
        $ssXml   = simplexml_load_file($tempDir . '/xl/sharedStrings.xml');
        foreach ($ssXml->si as $si) {
            $p = [];
            if (isset($si->t)) foreach ($si->t as $tv) $p[] = (string)$tv;
            if (isset($si->r)) foreach ($si->r as $r) if (isset($r->t)) $p[] = (string)$r->t;
            $strings[] = implode('', $p);
        }

        $wsXml = simplexml_load_file($tempDir . '/xl/worksheets/sheet1.xml');
        $rows  = $wsXml->sheetData->row;

        $this->command->info('Satirlar: ' . (count($rows) - 1));

        $now = now()->toDateTimeString();
        $updated = $inserted = $skipped = $count = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                if ((int)$row['r'] === 1) continue;

                $get = fn(int $ci) => $this->cell($row, $ci, $strings);

                $idRaw  = $get(0);
                $nameTr = $get(1); $nameEn = $get(2);
                $nameDe = $get(3); $nameRu = $get(4);

                if (!$nameTr && !$nameEn) { $skipped++; continue; }

                $id = ($idRaw !== '' && is_numeric($idRaw)) ? (int)round((float)$idRaw) : null;

                $name = array_filter([
                    'tr' => $nameTr ?: null, 'en' => $nameEn ?: null,
                    'de' => $nameDe ?: null, 'ru' => $nameRu ?: null,
                ]);

                $calRaw = $get(6);
                $ingTr  = $get(14); $ingEn = $get(15);
                $ingDe  = $get(16); $ingRu = $get(17);

                $ingredients = [];
                foreach (['tr'=>$ingTr,'en'=>$ingEn,'de'=>$ingDe,'ru'=>$ingRu] as $lang => $raw) {
                    $raw = trim($raw);
                    if (!$raw || $raw === '-') continue;
                    $items = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*|\r\n|\n|\r/', $raw))));
                    if (!empty($items)) $ingredients[$lang] = $items;
                }

                $record = [
                    'name'          => json_encode($name, JSON_UNESCAPED_UNICODE),
                    'description'   => null,
                    'ingredients'   => !empty($ingredients) ? json_encode($ingredients, JSON_UNESCAPED_UNICODE) : null,
                    'calories'      => ($calRaw !== '' && is_numeric($calRaw)) ? (int)round((float)$calRaw) : null,
                    'allergens'     => json_encode($this->parseAllergens($get(10), $get(11)), JSON_UNESCAPED_UNICODE),
                    'category'      => $this->mapCat($get(5)),
                    'is_vegan'      => $get(7) === 'Evet' ? 1 : 0,
                    'is_vegetarian' => $get(8) === 'Evet' ? 1 : 0,
                    'is_halal'      => $get(9) === 'Evet' ? 1 : 0,
                    'is_active'     => $get(19) !== 'Pasif' ? 1 : 0,
                    'sort_order'    => 0,
                    'updated_at'    => $now,
                ];

                if ($id && DB::table('food_labels')->where('id', $id)->exists()) {
                    DB::table('food_labels')->where('id', $id)->update($record);
                    $updated++;
                } else {
                    $record += ['branch_id'=>null,'created_by'=>null,'qr_token'=>(string)Str::uuid(),'created_at'=>$now];
                    DB::table('food_labels')->insert($record);
                    $inserted++;
                }

                $count++;
                if ($count % 500 === 0) $this->command->info("  -> $count (guncellendi:$updated yeni:$inserted)...");
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('HATA: ' . $e->getMessage());
            return;
        }

        $this->rrmdir($tempDir);
        $this->command->info("Tamamlandi: $count islendi — $updated guncellendi, $inserted yeni, $skipped atlandi.");
    }

    private function cell($row, int $ci, array $ss): string
    {
        foreach ($row->c as $c) {
            preg_match('/^([A-Z]+)/', (string)$c['r'], $m);
            $n = 0;
            foreach (str_split($m[1]) as $ch) $n = $n * 26 + (ord($ch) - 64);
            if (($n - 1) !== $ci) continue;
            return (string)$c['t'] === 's' ? ($ss[(int)(string)$c->v] ?? '') : (string)$c->v;
        }
        return '';
    }

    private function mapCat(string $raw): ?string
    {
        $raw = trim($raw);
        foreach ([
            'dessert'=>'Tatl','main'=>'Ana Yemek','soup'=>'orba','salad'=>'Salata',
            'appetizer'=>'Meze','side'=>'Yan Yemek','beverage'=>'ecek',
            'breakfast'=>'ahvalt','other'=>'i',
        ] as $key => $frag) {
            if (mb_stripos($raw, $frag) !== false) return $key;
        }
        return null;
    }

    private function parseAllergens(string $tr, string $en): array
    {
        $trMap = [
            'Gluten'=>'gluten','Kabuklu Deniz'=>'crustaceans','Kabuklu Yemi'=>'nuts',
            'Yumurta'=>'eggs','Bal'=>'fish','Yer F'=>'peanuts','Soya'=>'soybeans',
            'Laktoz'=>'milk','Kereviz'=>'celery','Hardal'=>'mustard','Susam'=>'sesame',
            'lfit'=>'sulphites','Bakla'=>'lupin','Yumu'=>'molluscs',
        ];
        $enMap = [
            'Gluten'=>'gluten','Crustaceans'=>'crustaceans','Eggs'=>'eggs','Fish'=>'fish',
            'Peanuts'=>'peanuts','Soybeans'=>'soybeans','Soy'=>'soybeans','Milk'=>'milk',
            'Nuts'=>'nuts','Celery'=>'celery','Mustard'=>'mustard','Sesame'=>'sesame',
            'Sulphites'=>'sulphites','Lupin'=>'lupin','Molluscs'=>'molluscs',
        ];
        $no   = ['yok','no','none','-','','keine'];
        $keys = [];
        if (!in_array(mb_strtolower(trim($tr)), $no)) {
            foreach (preg_split('/[,;\/]+/', $tr) as $part) {
                $part = trim($part);
                foreach ($trMap as $frag => $key) {
                    if (mb_stripos($part, $frag) !== false) { $keys[] = $key; break; }
                }
            }
        }
        if (empty($keys) && !in_array(mb_strtolower(trim($en)), $no)) {
            foreach (preg_split('/[,;\/]+/', $en) as $part) {
                $part = trim($part);
                foreach ($enMap as $frag => $key) {
                    if (mb_stripos($part, $frag) !== false) { $keys[] = $key; break; }
                }
            }
        }
        return array_values(array_unique($keys));
    }

    private function rrmdir(string $d): void
    {
        if (!is_dir($d)) return;
        foreach (scandir($d) as $f) {
            if ($f === '.' || $f === '..') continue;
            $p = "$d/$f"; is_dir($p) ? $this->rrmdir($p) : @unlink($p);
        }
        @rmdir($d);
    }
}