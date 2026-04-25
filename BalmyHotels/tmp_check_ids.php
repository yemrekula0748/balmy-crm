<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Branch;
use App\Models\FoodCategory;

echo "=== BRANCHES ===" . PHP_EOL;
Branch::select('id','name')->get()->each(function($b) {
    echo $b->id . ' | ' . $b->name . PHP_EOL;
});

echo PHP_EOL . "=== FOOD CATEGORIES ===" . PHP_EOL;
FoodCategory::orderBy('branch_id')->orderBy('sort_order')->get()->each(function($c) {
    echo $c->id . ' | branch=' . $c->branch_id . ' | ' . $c->getTitle() . PHP_EOL;
});
