<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fp = App\Models\FoodProduct::find(655);
echo "FoodProduct id=" . $fp->id . " price=" . var_export($fp->getRawOriginal('price'), true) . PHP_EOL;

$items = App\Models\QrMenuItem::where('food_product_id', 655)->get();
foreach ($items as $it) {
    // Find which menu this belongs to via category
    $cat = $it->category()->first();
    echo "QrMenuItem id={$it->id} cat_id={$it->category_id} (cat menu_id=" . ($cat ? $cat->qr_menu_id : '?') . ")"
        . " price=" . var_export($it->getRawOriginal('price'), true)
        . " price_override=" . var_export($it->getRawOriginal('price_override'), true)
        . " effectivePrice=" . var_export($it->effectivePrice(), true)
        . PHP_EOL;
}
