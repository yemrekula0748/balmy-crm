<?php

namespace App\Console\Commands;

use App\Models\FoodProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportFoodProducts extends Command
{
    protected $signature   = 'import:food-products
                                {--excel=datascrape/alkol_urunler.xlsx : Excel dosya yolu (proje kökünden)}
                                {--images=datascrape/images : Resim klasörü (proje kökünden)}
                                {--branch=2 : Branch ID}
                                {--category=11 : FoodCategory ID}';

    protected $description = 'Excel dosyasından yemek kütüphanesine ürün aktarır ve resimleri kopyalar.';

    public function handle(): int
    {
        $excelPath    = base_path($this->option('excel'));
        $imagesDir    = base_path($this->option('images'));
        $branchId     = (int) $this->option('branch');
        $categoryId   = (int) $this->option('category');

        if (! file_exists($excelPath)) {
            $this->error("Excel dosyası bulunamadı: {$excelPath}");
            return self::FAILURE;
        }

        $this->info("Excel okunuyor: {$excelPath}");

        $spreadsheet = IOFactory::load($excelPath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false); // 0-indexed

        // İlk satır başlık satırı
        if (count($rows) < 2) {
            $this->error('Excel boş veya sadece başlık satırı var.');
            return self::FAILURE;
        }

        // Başlık eşlemesi (0-indexed columns)
        // 0: resim Adı | 1: ürün adı tr | 2: ürün adı en | 3: ürün adı de | 4: ürün adı ru
        // 5: marka | 6: Alkol Oranı | 7: içindekiler tr | 8: içindekiler en | 9: içindekiler de | 10: içindekiler ru

        $created = 0;
        $skipped = 0;

        // Storage disk: public → storage/app/public/food_library/
        Storage::disk('public')->makeDirectory('food_library');

        $bar = $this->output->createProgressBar(count($rows) - 1);
        $bar->start();

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // başlık satırını atla

            $imageName = trim((string) ($row[0] ?? ''));
            $titleTr   = trim((string) ($row[1] ?? ''));
            $titleEn   = trim((string) ($row[2] ?? ''));
            $titleDe   = trim((string) ($row[3] ?? ''));
            $titleRu   = trim((string) ($row[4] ?? ''));
            $brand     = trim((string) ($row[5] ?? ''));
            $alcohol   = trim((string) ($row[6] ?? ''));
            $ingTr     = trim((string) ($row[7] ?? ''));
            $ingEn     = trim((string) ($row[8] ?? ''));
            $ingDe     = trim((string) ($row[9] ?? ''));
            $ingRu     = trim((string) ($row[10] ?? ''));

            if ($titleTr === '') {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Resmi kopyala
            $imagePath = null;
            if ($imageName !== '') {
                $srcFile = rtrim($imagesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imageName;
                if (file_exists($srcFile)) {
                    $destName  = 'food_library/' . $imageName;
                    Storage::disk('public')->put($destName, file_get_contents($srcFile));
                    $imagePath = $destName;
                } else {
                    $this->newLine();
                    $this->warn("  Resim bulunamadı: {$srcFile}");
                }
            }

            // Çok dilli başlık
            $title = array_filter([
                'tr' => $titleTr,
                'en' => $titleEn,
                'de' => $titleDe,
                'ru' => $titleRu,
            ]);

            // İçindekiler
            $ingredients = array_filter([
                'tr' => $ingTr,
                'en' => $ingEn,
                'de' => $ingDe,
                'ru' => $ingRu,
            ]);

            // Opsiyonlar: Marka + Alkol Oranı
            $options = [];
            if ($brand !== '') {
                $options[] = [
                    'label' => ['tr' => 'Marka', 'en' => 'Brand', 'de' => 'Marke', 'ru' => 'Марка'],
                    'type'  => 'text',
                    'value' => $brand,
                ];
            }
            if ($alcohol !== '') {
                $options[] = [
                    'label' => ['tr' => 'Alkol Oranı', 'en' => 'Alcohol Content', 'de' => 'Alkoholgehalt', 'ru' => 'Содержание алкоголя'],
                    'type'  => 'text',
                    'value' => $alcohol,
                ];
            }

            FoodProduct::create([
                'branch_id'        => $branchId,
                'food_category_id' => $categoryId,
                'printer_id'       => null,
                'title'            => $title,
                'description'      => null,
                'ingredients'      => $ingredients ?: null,
                'price'            => null,
                'image'            => $imagePath,
                'badges'           => null,
                'allergens'        => null,
                'options'          => $options ?: null,
                'calories'         => null,
                'protein'          => null,
                'carbs'            => null,
                'fat'              => null,
                'is_active'        => true,
                'sort_order'       => 0,
            ]);

            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Tamamlandı. Oluşturulan: {$created} | Atlanan (boş): {$skipped}");

        return self::SUCCESS;
    }
}
