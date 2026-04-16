<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;

$ids = [502,501,490,497,498,499,500,495,492,491,490,489,488,486,485,484,483,482,481,480];
$ids = array_unique($ids);
$image = 'food_library/h4bEIzvF1AGLfDJmlfJujj6KZ6T4TD33hk6toadT.png';

$updated = 0;
foreach ($ids as $id) {
    $product = FoodProduct::find($id);
    if (!$product) {
        echo "BULUNAMADI: #{$id}\n";
        continue;
    }
    $product->update(['image' => $image]);
    echo "GUNCELLENDI: #{$id} — {$product->getTitle('tr')}\n";
    $updated++;
}

echo "\n✓ Tamamlandı: {$updated} ürünün resmi güncellendi.\n";
