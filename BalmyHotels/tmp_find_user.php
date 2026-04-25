<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('email', 'garson3@balmyforesta.com')->first();
if ($user) {
    print_r($user->toArray());
} else {
    echo 'Kullanici bulunamadi.' . PHP_EOL;
}
