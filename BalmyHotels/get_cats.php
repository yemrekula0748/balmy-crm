<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;

// #433 stray duplicate — sil
$fp = FoodProduct::find(433);
if ($fp) {
    $title = is_array($fp->title) ? ($fp->title['tr'] ?? '?') : $fp->title;
    $fp->delete();
    echo "Silindi: FoodProduct #433 ({$title})" . PHP_EOL;
} else {
    echo "FoodProduct #433 bulunamadı." . PHP_EOL;
}
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;
use App\Models\QrMenuItem;

// Eşleştirme: food_product_id => qr_menu_category_id
$map = [
    // Başlangıçlar (Cat 27)
    415 => ['cat' => 27, 'sort' => 1],
    416 => ['cat' => 27, 'sort' => 2],
    417 => ['cat' => 27, 'sort' => 3],
    // Ara Sıcaklar (Cat 28)
    418 => ['cat' => 28, 'sort' => 1],
    419 => ['cat' => 28, 'sort' => 2],
    420 => ['cat' => 28, 'sort' => 3],
    421 => ['cat' => 28, 'sort' => 4],
    422 => ['cat' => 28, 'sort' => 5],
    423 => ['cat' => 28, 'sort' => 6],
    424 => ['cat' => 28, 'sort' => 7],
    // Salatalar (Cat 29)
    425 => ['cat' => 29, 'sort' => 1],
    426 => ['cat' => 29, 'sort' => 2],
    // Ana Yemekler (Cat 30)
    427 => ['cat' => 30, 'sort' => 1],
    428 => ['cat' => 30, 'sort' => 2],
    429 => ['cat' => 30, 'sort' => 3],
    // Tatlılar (Cat 31)
    430 => ['cat' => 31, 'sort' => 1],
    431 => ['cat' => 31, 'sort' => 2],
    432 => ['cat' => 31, 'sort' => 3],
];

$created = 0;
foreach ($map as $fpId => $meta) {
    $fp = FoodProduct::find($fpId);
    if (!$fp) {
        echo "  ✗ FoodProduct #{$fpId} bulunamadı" . PHP_EOL;
        continue;
    }

    $item = QrMenuItem::create([
        'category_id'     => $meta['cat'],
        'food_product_id' => $fp->id,
        'title'           => $fp->title,
        'description'     => $fp->description,
        'price'           => $fp->price,
        'price_override'  => null,
        'image'           => $fp->image,
        'is_active'       => true,
        'is_featured'     => false,
        'badges'          => $fp->badges,
        'sort_order'      => $meta['sort'],
    ]);

    $title = is_array($fp->title) ? ($fp->title['tr'] ?? '?') : $fp->title;
    echo "  ✓ [{$item->id}] {$title} → Cat #{$meta['cat']}" . PHP_EOL;
    $created++;
}

echo PHP_EOL . "Tamamlandı: {$created} ürün QR menü 9'a bağlandı." . PHP_EOL;

$rows = DB::table('qr_menu_items')
    ->select('id', 'category_id', 'food_product_id', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) as tr_title"))
    ->orderBy('id', 'desc')
    ->limit(25)
    ->get();
echo "Toplam bulk: " . DB::table('qr_menu_items')->count() . " kayıt" . PHP_EOL;
foreach ($rows as $r) {
    echo "  [{$r->id}] cat:{$r->category_id} | fp:{$r->food_product_id} | {$r->tr_title}" . PHP_EOL;
}

// food_products son 20
echo PHP_EOL . "=== food_products (son 20) ===" . PHP_EOL;
$fps = DB::table('food_products')
    ->select('id', 'food_category_id', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) as tr_title"))
    ->orderBy('id', 'desc')
    ->limit(20)
    ->get();
echo "Toplam: " . DB::table('food_products')->count() . " kayıt" . PHP_EOL;
foreach ($fps as $fp) {
    echo "  #{$fp->id} libcat:{$fp->food_category_id} | {$fp->tr_title}" . PHP_EOL;
}

// Tüm qr_menu_items id:52-69 doğrudan sorgula
$rows = DB::table('qr_menu_items')
    ->whereBetween('qr_menu_items.id', [52, 69])
    ->join('qr_menu_categories', 'qr_menu_items.category_id', '=', 'qr_menu_categories.id')
    ->leftJoin('food_products', 'qr_menu_items.food_product_id', '=', 'food_products.id')
    ->select(
        'qr_menu_items.id',
        'qr_menu_items.category_id',
        'qr_menu_categories.qr_menu_id',
        'qr_menu_items.food_product_id',
        'food_products.food_category_id',
        DB::raw("JSON_UNQUOTE(JSON_EXTRACT(qr_menu_items.title, '$.tr')) as item_title"),
        DB::raw("CASE WHEN food_products.ingredients IS NOT NULL THEN '✓ içindekiler' ELSE '✗ yok' END as has_ingredients")
    )
    ->orderBy('qr_menu_items.id')
    ->get();

echo "ID | qr_menu_id | cat_id | fp_id | lib_cat | içindekiler | başlık" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
foreach ($rows as $r) {
    echo "[{$r->id}] | menu:{$r->qr_menu_id} | qrcat:{$r->category_id} | fp:#{$r->food_product_id} | libcat:{$r->food_category_id} | {$r->has_ingredients} | {$r->item_title}" . PHP_EOL;
}
