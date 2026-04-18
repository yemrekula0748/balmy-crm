<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// İçecekler kategorileri: menu_id => cat_id
$map = [8 => 109, 10 => 108, 11 => 110, 12 => 111, 13 => 112, 14 => 113, 15 => 114];

foreach ($map as $mid => $catId) {
    $items = App\Models\QrMenuItem::where('category_id', $catId)
        ->orderBy('sort_order')
        ->get(['id', 'sort_order', 'sub_heading', 'title']);

    // Sub-heading gruplarını topla
    $groups = [];
    $currentGroup = '(başlıksız)';
    foreach ($items as $item) {
        $sh = is_array($item->sub_heading) ? ($item->sub_heading['tr'] ?? array_values(array_filter((array)$item->sub_heading))[0] ?? '') : ($item->sub_heading ?? '');
        if ($sh && $sh !== $currentGroup) {
            $currentGroup = $sh;
        }
        $groups[$currentGroup][] = $item->id;
    }

    echo "=== Menu {$mid} - İçecekler (cat {$catId}) ===\n";
    foreach ($groups as $groupName => $ids) {
        echo "  [" . count($ids) . " ürün] Ayraç: {$groupName}\n";
    }
    echo "\n";
}
