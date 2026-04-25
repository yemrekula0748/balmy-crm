<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
foreach (\App\Models\Branch::select('id','name')->orderBy('id')->get() as $b) {
    echo $b->id . ' | ' . $b->name . PHP_EOL;
}
