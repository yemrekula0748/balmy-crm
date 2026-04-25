<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QrMenuCategory;
use App\Models\QrMenuItem;

echo "=== MENU 10 CATEGORIES ===\n";
$cats10 = QrMenuCategory::where('qr_menu_id', 10)->orderBy('sort_order')->get();
foreach ($cats10 as $c) {
    $title = is_array($c->title) ? ($c->title['tr'] ?? json_encode($c->title)) : $c->title;
    echo "  CAT id={$c->id} sort={$c->sort_order} title={$title} sub_headings=" . json_encode($c->sub_headings) . "\n";
    $items = QrMenuItem::where('category_id', $c->id)->orderBy('sort_order')->get();
    foreach ($items as $it) {
        $ititle = is_array($it->title) ? ($it->title['tr'] ?? json_encode($it->title)) : $it->title;
        echo "    ITEM id={$it->id} sort={$it->sort_order} fp_id={$it->food_product_id} sub_heading=" . json_encode($it->sub_heading) . " title={$ititle} price={$it->price}\n";
    }
}

echo "\n=== MENU 18 CATEGORIES ===\n";
$cats18 = QrMenuCategory::where('qr_menu_id', 18)->orderBy('sort_order')->get();
foreach ($cats18 as $c) {
    $title = is_array($c->title) ? ($c->title['tr'] ?? json_encode($c->title)) : $c->title;
    echo "  CAT id={$c->id} sort={$c->sort_order} title={$title} sub_headings=" . json_encode($c->sub_headings) . "\n";
    $items = QrMenuItem::where('category_id', $c->id)->orderBy('sort_order')->get();
    foreach ($items as $it) {
        $ititle = is_array($it->title) ? ($it->title['tr'] ?? json_encode($it->title)) : $it->title;
        echo "    ITEM id={$it->id} sort={$it->sort_order} fp_id={$it->food_product_id} sub_heading=" . json_encode($it->sub_heading) . " title={$ititle} price={$it->price} price_glass={$it->price_glass} price_bottle={$it->price_bottle}\n";
    }
}
