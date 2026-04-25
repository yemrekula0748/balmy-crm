<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ŞUBELER ===\n";
foreach (\App\Models\Branch::select('id','name')->orderBy('id')->get() as $b) {
    echo $b->id . ' | ' . $b->name . "\n";
}

echo "\n=== KATEGORİLER ===\n";
foreach (\App\Models\FoodCategory::with('branch')->orderBy('branch_id')->orderBy('sort_order')->get() as $c) {
    $tr = $c->title['tr'] ?? '-';
    $en = $c->title['en'] ?? '';
    $branch = $c->branch ? $c->branch->name : 'NULL';
    echo $c->id . ' | branch:' . ($c->branch_id ?? 'null') . " ({$branch}) | TR:{$tr} | EN:{$en}\n";
}

echo "\n=== BADGE OPTIONS ===\n";
echo implode(', ', \App\Models\FoodProduct::BADGE_OPTIONS) . "\n";

echo "\n=== ALLERGENS ===\n";
foreach (\App\Models\FoodProduct::ALLERGENS as $key => $val) {
    echo $key . ' => ' . ($val['tr'] ?? '') . "\n";
}
