<?php
/**
 * Menu 18'deki tüm ürünleri, Menu 10'un İçecekler kategorisine (cat_id=108)
 * alt grup (sub_heading) eşleştirmesiyle ekler.
 * Zaten mevcut fp_id'leri atlar.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QrMenuCategory;
use App\Models\QrMenuItem;
use Illuminate\Support\Facades\DB;

$targetCatId = 108; // Menu 10 - İçecekler

// 1. Cat 108'in sub_headings dizisini al, tr -> full object map yap
$targetCat = QrMenuCategory::findOrFail($targetCatId);
$subHeadings = $targetCat->sub_headings ?? [];

$subHeadingByTr = [];
foreach ($subHeadings as $sh) {
    $tr = $sh['tr'] ?? null;
    if ($tr) {
        $subHeadingByTr[$tr] = $sh;
    }
}

// 2. Cat 108'de zaten mevcut fp_id'leri topla
$existingFpIds = QrMenuItem::where('category_id', $targetCatId)
    ->whereNotNull('food_product_id')
    ->pluck('food_product_id')
    ->toArray();
echo "Mevcut fp_id'ler (" . count($existingFpIds) . "): " . implode(', ', $existingFpIds) . PHP_EOL;

// Mevcut en yüksek sort_order'ı bul
$maxSort = QrMenuItem::where('category_id', $targetCatId)->max('sort_order') ?? -1;
$sortCounter = (int)$maxSort + 1;

// 3. Menu 18 kategorilerini sort_order'a göre sırala
$cats18 = QrMenuCategory::where('qr_menu_id', 18)->orderBy('sort_order')->get();

$added = 0;
$skipped = 0;

DB::beginTransaction();
try {
    foreach ($cats18 as $cat18) {
        // Bu kategoriyi menu 10'daki sub_heading ile eşleştir
        $catTitleTr = is_array($cat18->title) ? ($cat18->title['tr'] ?? null) : $cat18->title;

        // Normalizasyon: bazı kategori isimleri biraz farklı olabilir
        $subHeading = $subHeadingByTr[$catTitleTr] ?? null;

        // Eğer bulunamazsa fallback: tr başlığı aynı olan sub_heading
        if (!$subHeading) {
            foreach ($subHeadings as $sh) {
                if (mb_strtolower($sh['tr'] ?? '') === mb_strtolower((string)$catTitleTr)) {
                    $subHeading = $sh;
                    break;
                }
            }
        }

        if (!$subHeading) {
            echo "UYARI: '{$catTitleTr}' için sub_heading bulunamadı! Atlanıyor.\n";
            continue;
        }

        // Bu kategorinin ürünlerini al
        $items18 = QrMenuItem::where('category_id', $cat18->id)->orderBy('sort_order')->get();

        foreach ($items18 as $item) {
            if ($item->food_product_id && in_array($item->food_product_id, $existingFpIds)) {
                echo "  SKIP fp_id={$item->food_product_id} ({$catTitleTr} → zaten mevcut)\n";
                $skipped++;
                continue;
            }

            $newItem = QrMenuItem::create([
                'category_id'    => $targetCatId,
                'food_product_id'=> $item->food_product_id,
                'title'          => $item->getRawOriginal('title'),
                'description'    => $item->getRawOriginal('description'),
                'price'          => $item->price,
                'price_override' => $item->price_override,
                'image'          => $item->getRawOriginal('image'),
                'is_active'      => $item->is_active,
                'is_featured'    => $item->is_featured,
                'badges'         => $item->getRawOriginal('badges'),
                'sort_order'     => $sortCounter++,
                'sub_heading'    => json_encode($subHeading, JSON_UNESCAPED_UNICODE),
                'price_glass'    => $item->price_glass,
                'price_bottle'   => $item->price_bottle,
                'cl_glass'       => $item->cl_glass,
                'cl_bottle'      => $item->cl_bottle,
            ]);

            $itTitle = is_array($item->title) ? ($item->title['tr'] ?? '') : $item->title;
            echo "  ADD id={$newItem->id} fp_id={$item->food_product_id} [{$catTitleTr}] {$itTitle}\n";

            // Yeni eklenen fp_id'yi mevcut listeye ekle (çift eklemeyi önle)
            if ($item->food_product_id) {
                $existingFpIds[] = $item->food_product_id;
            }
            $added++;
        }
    }

    DB::commit();
    echo "\n=== TAMAMLANDI: {$added} ürün eklendi, {$skipped} ürün atlandı ===\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "HATA: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
