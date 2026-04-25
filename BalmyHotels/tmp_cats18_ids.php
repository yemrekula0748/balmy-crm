<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\QrMenuCategory;

$cats = QrMenuCategory::where('qr_menu_id', 18)->orderBy('sort_order')->get(['id','title']);
foreach ($cats as $c) {
    echo $c->id . ' => ' . $c->getTitle('tr') . PHP_EOL;
}
