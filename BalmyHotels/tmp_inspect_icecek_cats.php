<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$menuIds = [8, 10, 11, 12, 13, 14, 15];

foreach ($menuIds as $mid) {
    $menu = App\Models\QrMenu::with(['categories' => function($q) {
        $q->orderBy('sort_order')->with(['items' => function($qi) {
            $qi->orderBy('sort_order');
        }]);
    }])->find($mid);

    if (!$menu) { echo "Menu $mid bulunamadı\n"; continue; }

    echo "=== QrMenu id={$mid} name={$menu->name} ===\n";

    foreach ($menu->categories as $cat) {
        $titleRaw = is_array($cat->title) ? json_encode($cat->title, JSON_UNESCAPED_UNICODE) : $cat->title;
        $type = $cat->type ?? 'category';
        echo "  [cat id={$cat->id} sort={$cat->sort_order} type={$type} is_active=" . ($cat->is_active ? '1' : '0') . "] $titleRaw\n";
        foreach ($cat->items as $item) {
            $titleRaw = is_array($item->title) ? json_encode($item->title, JSON_UNESCAPED_UNICODE) : $item->title;
            echo "    [item id={$item->id} sort={$item->sort_order} fp_id={$item->food_product_id} price={$item->getRawOriginal('price')} po={$item->getRawOriginal('price_override')}] $titleRaw\n";
        }
    }
    echo "\n";
}
