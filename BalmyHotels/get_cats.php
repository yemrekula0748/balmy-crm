<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\QrMenu;
use App\Models\QrMenuCategory;

$menu = QrMenu::find(9);
echo "Menu: " . json_encode($menu->title) . " | branch_id=" . $menu->branch_id . PHP_EOL;
$cats = QrMenuCategory::where('qr_menu_id', 9)->orderBy('sort_order')->get(['id','title','sort_order']);
foreach ($cats as $c) {
    echo $c->id . ' | ' . json_encode($c->title) . ' | sort:' . $c->sort_order . PHP_EOL;
}
