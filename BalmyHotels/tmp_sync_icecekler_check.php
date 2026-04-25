<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Menu 18'deki İçecekler kategorisini bul
$menu18 = App\Models\QrMenu::find(18);
echo "=== QrMenu 18: {$menu18->name} ===\n";
$cats = $menu18->categories()->orderBy('sort_order')->get();
foreach ($cats as $cat) {
    $tr = is_array($cat->title) ? ($cat->title['tr'] ?? '?') : $cat->title;
    $count = $cat->items()->count();
    echo "  [cat id={$cat->id} sort={$cat->sort_order}] {$tr} ({$count} ürün)\n";
}

// İçecekler kategorisini bul
$icecekCat18 = $cats->first(function($c) {
    $title = is_array($c->title) ? ($c->title['tr'] ?? '') : ($c->title ?? '');
    return stripos($title, 'içecek') !== false;
});

if (!$icecekCat18) {
    echo "\nMenu 18'de İçecekler kategorisi bulunamadı!\n";
    exit;
}

echo "\n=== Menu 18 - İçecekler (cat {$icecekCat18->id}) - Ayraç Grupları ===\n";
$items = $icecekCat18->items()->orderBy('sort_order')->get();
$groups = [];
$currentGroup = '(başlıksız)';
foreach ($items as $item) {
    $sh = is_array($item->sub_heading) ? ($item->sub_heading['tr'] ?? array_values(array_filter((array)$item->sub_heading))[0] ?? '') : ($item->sub_heading ?? '');
    if ($sh && $sh !== $currentGroup) $currentGroup = $sh;
    $groups[$currentGroup][] = $item;
}
foreach ($groups as $g => $gitems) {
    echo "  [{$g}]: " . count($gitems) . " ürün\n";
}
echo "\nToplam: " . $items->count() . " ürün\n";

// Hedef kategoriler
echo "\n=== Hedef Kategoriler (Menu 8,10-15) ===\n";
$targetCats = [8 => 109, 10 => 108, 11 => 110, 12 => 111, 13 => 112, 14 => 113, 15 => 114];
foreach ($targetCats as $mid => $catId) {
    $count = App\Models\QrMenuItem::where('category_id', $catId)->count();
    echo "  Menu {$mid} cat {$catId}: {$count} ürün\n";
}
