<?php
require 'vendor/autoload.php';
$app    = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodProduct;
use Illuminate\Support\Facades\DB;

$IMAGE = 'food_library/mHm067xbGzkBP6XID9QhhaTfKctMgLUdCcvRotcm.png';

// 1. Resmi olmayan tüm ürünlere resim ekle
$imgUpdated = FoodProduct::where(function($q) {
    $q->whereNull('image')->orWhere('image', '');
})->update(['image' => $IMAGE]);

echo "Resim eklendi: {$imgUpdated} ürün güncellendi.\n";

// 2. Fiyatı null olan tüm ürünlere 0 fiyat gir
$priceUpdated = FoodProduct::whereNull('price')->update(['price' => 0]);

echo "Fiyat (0) girildi: {$priceUpdated} ürün güncellendi.\n";

echo "\nTamamlandı.\n";
