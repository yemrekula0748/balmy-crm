<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$targetMenuIds = [8, 10, 11, 12, 13, 14, 15];
foreach ($targetMenuIds as $mid) {
    $menu = App\Models\QrMenu::find($mid);
    echo "\n=== Menu {$mid}: {$menu->name} ===\n";
    $cats = $menu->categories()->orderBy('sort_order')->get();
    foreach ($cats as $cat) {
        $tr = is_array($cat->title) ? ($cat->title['tr'] ?? '?') : $cat->title;
        $count = $cat->items()->count();
        echo "  [cat id={$cat->id} sort={$cat->sort_order}] {$tr} ({$count} ürün)\n";
        
        // Check sub_headings if any
        $sh = $cat->sub_headings;
        if (!empty($sh)) {
            foreach ($sh as $s) {
                $stitle = is_array($s) ? ($s['tr'] ?? json_encode($s)) : $s;
                echo "    - sub_heading: {$stitle}\n";
            }
        }
    }
}

// Also check the currently stored cat IDs for the targets
echo "\n=== Hedef Kategorilerdeki İçecek Kategorileri (cat 108-114, 109) ===\n";
$oldTargetCats = [8 => 109, 10 => 108, 11 => 110, 12 => 111, 13 => 112, 14 => 113, 15 => 114];
foreach ($oldTargetCats as $mid => $catId) {
    $cat = App\Models\QrMenuCategory::find($catId);
    if (!$cat) {
        echo "Menu {$mid} cat {$catId}: BULUNAMADI\n";
        continue;
    }
    $tr = is_array($cat->title) ? ($cat->title['tr'] ?? '?') : $cat->title;
    $count = $cat->items()->count();
    echo "Menu {$mid} cat {$catId} ({$tr}): {$count} ürün\n";
    
    // Show sub_heading distribution
    $items = $cat->items()->orderBy('sort_order')->get();
    $groups = [];
    foreach ($items as $item) {
        $sh = is_array($item->sub_heading) ? ($item->sub_heading['tr'] ?? array_values(array_filter((array)$item->sub_heading))[0] ?? '') : ($item->sub_heading ?? '');
        $groups[$sh ?? '(başlıksız)'] = ($groups[$sh ?? '(başlıksız)'] ?? 0) + 1;
    }
    foreach ($groups as $g => $cnt) {
        echo "  [{$g}]: {$cnt} ürün\n";
    }
}
