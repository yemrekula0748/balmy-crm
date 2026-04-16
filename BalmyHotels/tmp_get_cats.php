<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (App\Models\FoodCategory::where('branch_id',2)->orderBy('id')->get(['id','title']) as $c) {
    echo $c->id.' '.json_encode($c->title, JSON_UNESCAPED_UNICODE).PHP_EOL;
}
