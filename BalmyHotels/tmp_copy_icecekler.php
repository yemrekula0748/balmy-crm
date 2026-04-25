<?php
/**
 * Menu 10'daki İçecekler kategorisini (cat_id=108) ve tüm ürünlerini
 * verilen hedef menülere kopyalar.
 * - Cast edilmiş array değerleri kullanılır (çift encode yok)
 * - Her hedef menü için yeni bir kategori oluşturulur
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QrMenuCategory;
use App\Models\QrMenuItem;
use Illuminate\Support\Facades\DB;

$targetMenuIds = [8, 11, 12, 13, 14, 15];
$sourceCatId   = 108; // Menu 10 - İçecekler

// Kaynak kategoriyi al (Eloquent cast → array)
$sourceCat = QrMenuCategory::findOrFail($sourceCatId);

// Kaynak ürünleri sort_order'a göre al
$sourceItems = QrMenuItem::where('category_id', $sourceCatId)
    ->orderBy('sort_order')
    ->get();

echo "Kaynak: cat_id={$sourceCatId}, " . count($sourceItems) . " ürün\n\n";

DB::beginTransaction();
try {
    foreach ($targetMenuIds as $menuId) {
        echo "=== MENU {$menuId} ===\n";

        // Bu menüde zaten İçecekler adında kategori var mı?
        $existingCat = QrMenuCategory::where('qr_menu_id', $menuId)
            ->whereJsonContains('title->tr', 'İçecekler')
            ->first();

        if ($existingCat) {
            echo "  SKIP: Zaten İçecekler kategorisi var (cat_id={$existingCat->id})\n\n";
            continue;
        }

        // En yüksek sort_order'ı bul
        $maxSort = QrMenuCategory::where('qr_menu_id', $menuId)->max('sort_order') ?? -1;
        $newSort = (int)$maxSort + 1;

        // Yeni kategori oluştur — Eloquent'e array geç, cast halleder
        $newCat = QrMenuCategory::create([
            'qr_menu_id'   => $menuId,
            'title'        => $sourceCat->title,        // array (cast)
            'description'  => $sourceCat->description,  // array|null (cast)
            'icon'         => $sourceCat->icon,
            'image'        => $sourceCat->image,
            'sort_order'   => $newSort,
            'is_active'    => $sourceCat->is_active,
            'sub_headings' => $sourceCat->sub_headings,  // array (cast)
        ]);

        echo "  Kategori oluşturuldu: cat_id={$newCat->id} sort={$newSort}\n";

        // Ürünleri kopyala
        $sortCounter = 0;
        foreach ($sourceItems as $item) {
            QrMenuItem::create([
                'category_id'     => $newCat->id,
                'food_product_id' => $item->food_product_id,
                'title'           => $item->title,           // array (cast)
                'description'     => $item->description,     // array|null (cast)
                'price'           => $item->price,
                'price_override'  => $item->price_override,
                'image'           => $item->getRawOriginal('image'), // görsel path, string
                'is_active'       => $item->is_active,
                'is_featured'     => $item->is_featured,
                'badges'          => $item->badges,          // array|null (cast)
                'sort_order'      => $sortCounter++,
                'sub_heading'     => $item->sub_heading,     // array|null (cast)
                'price_glass'     => $item->price_glass,
                'price_bottle'    => $item->price_bottle,
                'cl_glass'        => $item->cl_glass,
                'cl_bottle'       => $item->cl_bottle,
            ]);
        }

        echo "  " . count($sourceItems) . " ürün eklendi\n\n";
    }

    DB::commit();
    echo "=== TAMAMLANDI ===\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "HATA: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
