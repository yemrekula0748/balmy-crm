<?php

declare(strict_types=1);

$basePath = rtrim((string) (getenv('BALMY_APP_BASE') ?: __DIR__), '/\\');
require $basePath . '/vendor/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;
use App\Models\QrMenu;
use App\Models\QrMenuCategory;
use App\Models\QrMenuItem;
use App\Models\QrMenuLanguage;
use Illuminate\Support\Facades\DB;

const BRANCH_ID = 1;
const MENU_SLUG = 'alacarte';
const FIRST_PRODUCT_ID = 681;
const LAST_PRODUCT_ID = 728;

$run = in_array('--run', $argv, true);

$products = FoodProduct::with('foodCategory')
    ->where('branch_id', BRANCH_ID)
    ->whereBetween('id', [FIRST_PRODUCT_ID, LAST_PRODUCT_ID])
    ->where('is_active', true)
    ->orderBy('food_category_id')
    ->orderBy('sort_order')
    ->orderBy('id')
    ->get();

if ($products->count() !== 48) {
    throw new RuntimeException('Beklenen 48 Balmy Beach ürünü bulunamadı; bulunan: ' . $products->count());
}

$expectedCategoryTitles = [
    'Başlangıçlar',
    'Ara Sıcaklar',
    'Salatalar',
    'Ana Yemekler',
    'Tatlılar',
    'Sandviç & Burgerler',
    'Makarnalar',
    'Pide & Pizza',
    '7 Bölge 7 Lezzet',
];

$grouped = $products->groupBy('food_category_id');
$actualCategoryTitles = $grouped->map(
    fn ($items) => $items->first()->foodCategory?->getTitle('tr')
)->values()->all();

sort($expectedCategoryTitles);
$sortedActual = $actualCategoryTitles;
sort($sortedActual);
if ($sortedActual !== $expectedCategoryTitles) {
    throw new RuntimeException('Ürün kategorileri beklenen dokuz kategoriyle eşleşmiyor.');
}

$existing = QrMenu::where('name', MENU_SLUG)->first();
echo 'Menü slug: ' . MENU_SLUG . PHP_EOL;
echo 'Mevcut menü: ' . ($existing ? "#{$existing->id}" : 'yok') . PHP_EOL;
echo 'Kategori: ' . $grouped->count() . PHP_EOL;
echo 'Ürün: ' . $products->count() . PHP_EOL;

if (!$run) {
    echo "KURU KONTROL — değişiklik yapılmadı." . PHP_EOL;
    exit(0);
}

if ($existing) {
    throw new RuntimeException("'alacarte' slug'ı işlem öncesinde zaten mevcut; güvenlik için üzerine yazılmadı.");
}

$menuId = DB::transaction(function () use ($products, $grouped): int {
    $brandSource = QrMenu::where('branch_id', BRANCH_ID)->orderBy('id')->first();
    if (!$brandSource) {
        throw new RuntimeException('Balmy Beach için marka ayarları alınacak mevcut QR menü bulunamadı.');
    }

    $menu = QrMenu::create([
        'branch_id' => BRANCH_ID,
        'created_by' => $brandSource->created_by,
        'name' => MENU_SLUG,
        'title' => [
            'tr' => 'A La Carte Menü',
            'en' => 'À La Carte Menu',
            'de' => 'À-la-carte-Menü',
            'ru' => 'Меню à la carte',
        ],
        'description' => [
            'tr' => 'Balmy Beach Resort’un özenle hazırlanan A La Carte lezzetlerini keşfedin.',
            'en' => 'Discover the carefully crafted À La Carte flavors of Balmy Beach Resort.',
            'de' => 'Entdecken Sie die sorgfältig zubereiteten À-la-carte-Kreationen des Balmy Beach Resort.',
            'ru' => 'Откройте для себя тщательно приготовленные блюда à la carte от Balmy Beach Resort.',
        ],
        'logo' => $brandSource->logo,
        'cover_image' => $brandSource->cover_image,
        'theme_color' => $brandSource->theme_color ?: '#c19b77',
        'theme' => $brandSource->theme ?: 'light',
        'is_active' => true,
        'currency' => $brandSource->currency ?: 'EUR',
        'currency_symbol' => $brandSource->currency_symbol ?: '€',
    ]);

    $languages = [
        ['code'=>'tr','name'=>'Türkçe','flag'=>'🇹🇷','is_default'=>true,'sort_order'=>0],
        ['code'=>'en','name'=>'English','flag'=>'🇬🇧','is_default'=>false,'sort_order'=>1],
        ['code'=>'de','name'=>'Deutsch','flag'=>'🇩🇪','is_default'=>false,'sort_order'=>2],
        ['code'=>'ru','name'=>'Русский','flag'=>'🇷🇺','is_default'=>false,'sort_order'=>3],
    ];
    foreach ($languages as $language) {
        $menu->languages()->create($language);
    }

    $categoryOrder = [
        'Başlangıçlar' => 0,
        'Ara Sıcaklar' => 1,
        'Salatalar' => 2,
        'Ana Yemekler' => 3,
        'Tatlılar' => 4,
        'Sandviç & Burgerler' => 5,
        'Makarnalar' => 6,
        'Pide & Pizza' => 7,
        '7 Bölge 7 Lezzet' => 8,
    ];

    $categoryGroups = $grouped->sortBy(
        fn ($items) => $categoryOrder[$items->first()->foodCategory->getTitle('tr')] ?? 999
    );

    foreach ($categoryGroups as $items) {
        $libraryCategory = $items->first()->foodCategory;
        $categoryTitleTr = $libraryCategory->getTitle('tr');

        $category = QrMenuCategory::create([
            'qr_menu_id' => $menu->id,
            'title' => array_intersect_key((array) $libraryCategory->title, array_flip(['tr','en','de','ru'])),
            'description' => null,
            'icon' => $libraryCategory->icon,
            'sort_order' => $categoryOrder[$categoryTitleTr],
            'is_active' => true,
        ]);

        $itemSort = 0;
        foreach ($items->sortBy([['sort_order', 'asc'], ['id', 'asc']]) as $product) {
            QrMenuItem::create([
                'category_id' => $category->id,
                'food_product_id' => $product->id,
                'title' => array_intersect_key((array) $product->title, array_flip(['tr','en','de','ru'])),
                'description' => array_intersect_key((array) $product->description, array_flip(['tr','en','de','ru'])),
                'price' => 0,
                'price_override' => 0,
                'image' => $product->image,
                'is_active' => true,
                'is_featured' => false,
                'badges' => $product->badges,
                'sort_order' => $itemSort++,
            ]);
        }
    }

    return $menu->id;
});

echo "QR menü oluşturuldu: #{$menuId}" . PHP_EOL;
