<?php
/**
 * Menu 18 (icecekler) kategorilerindeki ürünleri,
 * Menu 8,10,11,12,13,14,15'deki tek "İçecekler" kategorilerine (cat 108-114,109) senkronize eder.
 *
 * Strateji:
 *  - Menu 18'deki her kategori → sub_heading olarak kullanılır
 *  - Hedef kategorilerdeki tüm itemlar silinip yeniden oluşturulur
 *  - food_product_id, title, description, image, badges, price/override/glass/bottle, cl_*, is_featured korunur
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// --- Kaynak: Menu 18 kategorileri ---
$sourceCats = App\Models\QrMenuCategory::where('qr_menu_id', 18)
    ->orderBy('sort_order')
    ->orderBy('id')
    ->get();

echo "Kaynak: Menu 18 - " . $sourceCats->count() . " kategori\n\n";

// Kaynak item düz listesi (sort_order = global sıra, sub_heading = kategori başlığı)
$sourceItems = [];
$globalSort = 0;
foreach ($sourceCats as $cat) {
    $catTitle = is_array($cat->title) ? $cat->title : json_decode($cat->title, true);
    $items = $cat->items()->orderBy('sort_order')->get();
    foreach ($items as $item) {
        $sourceItems[] = [
            'food_product_id' => $item->food_product_id,
            'title'           => is_array($item->title) ? $item->title : json_decode($item->title, true),
            'description'     => is_array($item->description) ? $item->description : json_decode($item->description, true),
            'sub_heading'     => $catTitle, // kategori adı ayraç olacak
            'price'           => $item->getRawOriginal('price'),
            'price_override'  => $item->getRawOriginal('price_override'),
            'price_glass'     => $item->getRawOriginal('price_glass'),
            'price_bottle'    => $item->getRawOriginal('price_bottle'),
            'cl_glass'        => $item->getRawOriginal('cl_glass'),
            'cl_bottle'       => $item->getRawOriginal('cl_bottle'),
            'image'           => $item->getRawOriginal('image'),
            'badges'          => is_array($item->badges) ? $item->badges : json_decode($item->badges ?? 'null', true),
            'is_active'       => true,
            'is_featured'     => (bool) $item->is_featured,
            'sort_order'      => $globalSort++,
        ];
    }
    echo "  Kategori [{$cat->id}] " . ($catTitle['tr'] ?? array_values($catTitle)[0] ?? '?') . ": " . $items->count() . " ürün\n";
}

echo "\nToplam kaynak ürün: " . count($sourceItems) . "\n\n";

// --- Hedef kategoriler: menu_id => cat_id ---
$targetCats = [8 => 109, 10 => 108, 11 => 110, 12 => 111, 13 => 112, 14 => 113, 15 => 114];

// DRY RUN kontrolü - ilk argüman "--run" değilse sadece rapor yaz
$isRun = in_array('--run', $argv ?? []);

if (!$isRun) {
    echo "=== DRY RUN (gerçek silme/ekleme yapılmadı) ===\n";
    echo "Çalıştırmak için: php tmp_sync_icecekler.php --run\n\n";
}

DB::beginTransaction();
try {
    foreach ($targetCats as $mid => $catId) {
        $existing = App\Models\QrMenuItem::where('category_id', $catId)->count();
        echo "Menu {$mid} (cat {$catId}): {$existing} ürün silinecek, " . count($sourceItems) . " eklenecek\n";

        if ($isRun) {
            // Tümünü sil
            App\Models\QrMenuItem::where('category_id', $catId)->delete();

            // Yeniden ekle
            $now = now();
            $rows = array_map(function($item) use ($catId, $now) {
                return array_merge($item, [
                    'category_id'  => $catId,
                    'title'        => json_encode($item['title'], JSON_UNESCAPED_UNICODE),
                    'description'  => json_encode($item['description'], JSON_UNESCAPED_UNICODE),
                    'sub_heading'  => json_encode($item['sub_heading'], JSON_UNESCAPED_UNICODE),
                    'badges'       => $item['badges'] ? json_encode($item['badges'], JSON_UNESCAPED_UNICODE) : null,
                    'is_active'    => 1,
                    'is_featured'  => (int) $item['is_featured'],
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }, $sourceItems);

            // Chunk insert (MySQL max placeholder limit)
            foreach (array_chunk($rows, 50) as $chunk) {
                DB::table('qr_menu_items')->insert($chunk);
            }
            echo "  → Tamamlandı\n";
        }
    }

    if ($isRun) {
        DB::commit();
        echo "\n✓ Sync başarılı!\n";
    } else {
        DB::rollBack();
        echo "\nDry run bitti. Onaylamak için --run ile çalıştırın.\n";
    }
} catch (\Exception $e) {
    DB::rollBack();
    echo "\nHATA: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
