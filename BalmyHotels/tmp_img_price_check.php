<?php
require 'vendor/autoload.php';
$app    = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodProduct;

// 508'in resmini al
$ref = FoodProduct::find(508);
echo "Referans (ID 508) image: " . $ref->image . "\n\n";

// Resmi olmayan ürün sayısı
$noImage = FoodProduct::whereNull('image')->orWhere('image','')->count();
echo "Resmi olmayan ürün sayısı: " . $noImage . "\n";

// Fiyatı null olan ürün sayısı
$noPrice = FoodProduct::whereNull('price')->count();
echo "Fiyatı null olan ürün sayısı: " . $noPrice . "\n";
