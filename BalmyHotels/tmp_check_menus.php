<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QrMenuCategory;
use App\Models\QrMenuItem;

$targetMenuIds = [8, 11, 12, 13, 14, 15];

foreach ($targetMenuIds as $menuId) {
    $cats = QrMenuCategory::where('qr_menu_id', $menuId)->orderBy('sort_order')->get(['id','sort_order','title']);
    echo "=== MENU {$menuId} ===\n";
    foreach ($cats as $c) {
        $t = is_array($c->title) ? ($c->title['tr'] ?? json_encode($c->title)) : $c->title;
        $count = QrMenuItem::where('category_id', $c->id)->count();
        echo "  cat_id={$c->id} sort={$c->sort_order} title={$t} items={$count}\n";
    }
    echo "\n";
}
