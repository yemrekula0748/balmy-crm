<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Tüm menülerdeki İçecekler kategorilerinin id listesi
$catIds = [109, 108, 110, 111, 112, 113, 114];

// Bir tanesine bak (108 = sunsetsnack menu 10) - tüm items
$cat = App\Models\QrMenuCategory::find(108);
echo "=== Kategori id=108 (menu 10 - İçecekler) ===\n";
$items = $cat->items()->orderBy('sort_order')->get();
foreach ($items as $item) {
    $titles = is_array($item->title) ? $item->title : json_decode($item->title, true);
    $tr = ($titles['tr'] ?? array_values((array)$titles)[0] ?? '—');
    $fp = $item->food_product_id ?? 'null';
    $price = $item->getRawOriginal('price');
    echo "  [id={$item->id} sort={$item->sort_order} fp_id={$fp} price={$price} active=" . ($item->is_active ? '1' : '0') . "] {$tr}\n";
}

echo "\n\n=== QrMenuCategory model - type alanı var mı? ===\n";
// Model sütunları
$cat2 = App\Models\QrMenuCategory::first();
echo implode(', ', array_keys($cat2->toArray())) . "\n";

echo "\n\n=== QrMenuCategory fillable ===\n";
echo implode(', ', $cat2->getFillable()) . "\n";
