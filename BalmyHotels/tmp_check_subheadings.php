<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check sub_headings on cat 109 (menu 8)
$cat = App\Models\QrMenuCategory::find(109);
$current = $cat->sub_headings;
echo "cat 109 current sub_headings:\n";
foreach ((array)$current as $i => $sh) {
    $t = is_array($sh) ? ($sh['tr'] ?? json_encode($sh)) : $sh;
    echo "  [$i] $t\n";
}

// What menu 18 cats order would produce
$sourceCats = App\Models\QrMenuCategory::where('qr_menu_id', 18)->orderBy('sort_order')->orderBy('id')->get();
echo "\nMenu 18 order (sort_order, id):\n";
foreach ($sourceCats as $c) {
    $t = is_array($c->title) ? ($c->title['tr'] ?? '?') : $c->title;
    echo "  [sort={$c->sort_order} id={$c->id}] $t\n";
}
