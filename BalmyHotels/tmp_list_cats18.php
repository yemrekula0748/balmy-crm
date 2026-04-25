<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\QrMenuCategory;

$cats = QrMenuCategory::where('qr_menu_id', 18)->orderBy('sort_order')->get();
$names = $cats->map(fn($c) => $c->getTitle('tr'))->implode(', ');
echo $names . PHP_EOL;
