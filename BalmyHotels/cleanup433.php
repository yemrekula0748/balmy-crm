<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;

$fp = FoodProduct::find(433);
if ($fp) {
    $title = is_array($fp->title) ? ($fp->title['tr'] ?? '?') : $fp->title;
    $fp->delete();
    echo "Silindi: FoodProduct #433 ({$title})" . PHP_EOL;
} else {
    echo "FoodProduct #433 bulunamadi." . PHP_EOL;
}
