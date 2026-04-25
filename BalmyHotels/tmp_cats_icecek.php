<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$menuIds = [8, 10, 11, 12, 13, 14, 15];

foreach ($menuIds as $mid) {
    $menu = App\Models\QrMenu::with(['categories' => function($q) {
        $q->orderBy('sort_order');
    }])->find($mid);

    if (!$menu) { echo "Menu $mid bulunamadı\n"; continue; }

    echo "=== QrMenu id={$mid} name={$menu->name} ===\n";

    foreach ($menu->categories as $cat) {
        $titles = is_array($cat->title) ? $cat->title : json_decode($cat->title, true);
        $tr = ($titles['tr'] ?? array_values((array)$titles)[0] ?? '—');
        $type = $cat->type ?? 'category';
        $active = $cat->is_active ? 'aktif' : 'pasif';
        $itemCount = $cat->items()->count();
        echo "  [id={$cat->id} sort={$cat->sort_order} type={$type} {$active} items={$itemCount}] {$tr}\n";
    }
    echo "\n";
}
