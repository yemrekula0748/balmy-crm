<?php
/**
 * Balmy Beach Resort – İçecekler Kategorisi Ürün Seeder
 * branch_id = 1, food_category_id = 63
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;

$branchId   = 1;
$categoryId = 63;

$products = [
    // ─── KAHVE ───────────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>'Espresso','en'=>'Espresso','de'=>'Espresso','ru'=>'Эспрессо','ar'=>'إسبريسو'],
        'price' => 4.00,
    ],
    [
        'title' => ['tr'=>'Macchiato','en'=>'Macchiato','de'=>'Macchiato','ru'=>'Макиато','ar'=>'ماكياتو'],
        'price' => 5.00,
    ],
    [
        'title' => ['tr'=>'Cafe Latte','en'=>'Cafe Latte','de'=>'Caffè Latte','ru'=>'Кафе Латте','ar'=>'كافيه لاتيه'],
        'price' => 5.00,
    ],
    [
        'title' => ['tr'=>'Cappuccino','en'=>'Cappuccino','de'=>'Cappuccino','ru'=>'Капучино','ar'=>'كابوتشينو'],
        'price' => 5.00,
    ],
    [
        'title' => ['tr'=>'Americano','en'=>'Americano','de'=>'Americano','ru'=>'Американо','ar'=>'أمريكانو'],
        'price' => 4.00,
    ],

    // ─── ÇAY ─────────────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>'Türk Çayı','en'=>'Turkish Tea','de'=>'Türkischer Tee','ru'=>'Турецкий чай','ar'=>'شاي تركي'],
        'price' => 2.00,
    ],
    [
        'title' => ['tr'=>'Bitki Çayları','en'=>'Herbal Tea','de'=>'Kräutertee','ru'=>'Травяной чай','ar'=>'شاي أعشاب'],
        'price' => 3.00,
    ],
    [
        'title' => ['tr'=>'Fresh Çaylar','en'=>'Fresh Tea','de'=>'Frischer Tee','ru'=>'Свежий чай','ar'=>'شاي طازج'],
        'price' => 5.00,
    ],

    // ─── MEYVE SULARI ────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>'Portakal Suyu','en'=>'Orange Juice','de'=>'Orangensaft','ru'=>'Апельсиновый сок','ar'=>'عصير برتقال'],
        'price' => 4.00,
    ],
    [
        'title' => ['tr'=>'Elma Suyu','en'=>'Apple Juice','de'=>'Apfelsaft','ru'=>'Яблочный сок','ar'=>'عصير تفاح'],
        'price' => 4.00,
    ],
    [
        'title' => ['tr'=>'Ananas Suyu','en'=>'Pineapple Juice','de'=>'Ananassaft','ru'=>'Ананасовый сок','ar'=>'عصير أناناس'],
        'price' => 7.00,
    ],
    [
        'title' => ['tr'=>'Vişne Suyu','en'=>'Cherry Juice','de'=>'Kirschsaft','ru'=>'Вишнёвый сок','ar'=>'عصير كرز'],
        'price' => 3.00,
    ],
    [
        'title' => ['tr'=>'Kayısı Suyu','en'=>'Apricot Juice','de'=>'Aprikosensaft','ru'=>'Абрикосовый сок','ar'=>'عصير مشمش'],
        'price' => 3.00,
    ],

    // ─── MEŞRUBATlar ────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>'Coca Cola','en'=>'Coca Cola','de'=>'Coca Cola','ru'=>'Кока-Кола','ar'=>'كوكا كولا'],
        'price' => 3.00,
    ],
    [
        'title' => ['tr'=>'Fanta','en'=>'Fanta','de'=>'Fanta','ru'=>'Фанта','ar'=>'فانتا'],
        'price' => 3.00,
    ],
    [
        'title' => ['tr'=>'Sprite','en'=>'Sprite','de'=>'Sprite','ru'=>'Спрайт','ar'=>'سبرايت'],
        'price' => 3.00,
    ],
    [
        'title' => ['tr'=>'Coca Cola Zero','en'=>'Coca Cola Zero','de'=>'Coca Cola Zero','ru'=>'Кока-Кола Зеро','ar'=>'كوكا كولا زيرو'],
        'price' => 3.00,
    ],
    [
        'title' => ['tr'=>'Soda','en'=>'Soda Water','de'=>'Sodawasser','ru'=>'Содовая','ar'=>'صودا'],
        'price' => 2.00,
    ],

    // ─── BİRA ────────────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>'Tuborg Şişe 33 cl','en'=>'Tuborg Bottle 33 cl','de'=>'Tuborg Flasche 33 cl','ru'=>'Туборг Бутылка 33 кл','ar'=>'توبورغ زجاجة 33 سل'],
        'price' => 8.00,
    ],
    [
        'title' => ['tr'=>'Miller Şişe 33 cl','en'=>'Miller Bottle 33 cl','de'=>'Miller Flasche 33 cl','ru'=>'Миллер Бутылка 33 кл','ar'=>'ميلر زجاجة 33 سل'],
        'price' => 8.00,
    ],
    [
        'title' => ['tr'=>'Corona Şişe','en'=>'Corona Bottle','de'=>'Corona Flasche','ru'=>'Корона Бутылка','ar'=>'كورونا زجاجة'],
        'price' => 8.00,
    ],
    [
        'title' => ['tr'=>'Carlsberg Şişe','en'=>'Carlsberg Bottle','de'=>'Carlsberg Flasche','ru'=>'Карлсберг Бутылка','ar'=>'كارلسبرغ زجاجة'],
        'price' => 8.00,
    ],
    [
        'title' => ['tr'=>'Budweiser Kutu 33 cl','en'=>'Budweiser Can 33 cl','de'=>'Budweiser Dose 33 cl','ru'=>'Будвайзер Банка 33 кл','ar'=>'بدوايزر علبة 33 سل'],
        'price' => 8.00,
    ],

    // ─── CİN ─────────────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>"Gordon's Cin",'en'=>"Gordon's Gin",'de'=>"Gordon's Gin",'ru'=>"Джин Gordon's",'ar'=>'جين غوردونز'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'Beefeater Cin','en'=>'Beefeater Gin','de'=>'Beefeater Gin','ru'=>'Джин Beefeater','ar'=>'جين بيفيتر'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>"Gilbey's Cin",'en'=>"Gilbey's Gin",'de'=>"Gilbey's Gin",'ru'=>"Джин Gilbey's",'ar'=>'جين جيلبيز'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'İstanbul Cin','en'=>'Istanbul Gin','de'=>'Istanbul Gin','ru'=>'Джин İstanbul','ar'=>'جين إسطنبول'],
        'price' => 0.00,
    ],

    // ─── VOTKA ───────────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>'Smirnoff Red Votka','en'=>'Smirnoff Red Vodka','de'=>'Smirnoff Red Wodka','ru'=>'Водка Smirnoff Red','ar'=>'فودكا سميرنوف ريد'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'Absolut Votka','en'=>'Absolut Vodka','de'=>'Absolut Wodka','ru'=>'Водка Absolut','ar'=>'فودكا أبسولوت'],
        'price' => 0.00,
    ],

    // ─── ROM ─────────────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>'Captain Morgan Rom','en'=>'Captain Morgan Rum','de'=>'Captain Morgan Rum','ru'=>'Ром Captain Morgan','ar'=>'رم كابتن مورغان'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'Havana Club Rom','en'=>'Havana Club Rum','de'=>'Havana Club Rum','ru'=>'Ром Havana Club','ar'=>'رم هافانا كلوب'],
        'price' => 0.00,
    ],

    // ─── TEKİLA ──────────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>'Olmeca Gold Tekila','en'=>'Olmeca Gold Tequila','de'=>'Olmeca Gold Tequila','ru'=>'Текила Olmeca Gold','ar'=>'تيكيلا أولميكا جولد'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'Sierra Blanco Tekila','en'=>'Sierra Blanco Tequila','de'=>'Sierra Blanco Tequila','ru'=>'Текила Sierra Blanco','ar'=>'تيكيلا سييرا بلانكو'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'Pueblo Tekila','en'=>'Pueblo Tequila','de'=>'Pueblo Tequila','ru'=>'Текила Pueblo','ar'=>'تيكيلا بويبلو'],
        'price' => 0.00,
    ],

    // ─── VİSKİ ───────────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>'J&B Viski','en'=>'J&B Whisky','de'=>'J&B Whisky','ru'=>'Виски J&B','ar'=>'ويسكي J&B'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'J.W. Red Label Viski','en'=>'Johnnie Walker Red Label','de'=>'Johnnie Walker Red Label','ru'=>'Виски Johnnie Walker Red Label','ar'=>'ويسكي جوني ووكر أحمر'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'J.W. Black Label Viski','en'=>'Johnnie Walker Black Label','de'=>'Johnnie Walker Black Label','ru'=>'Виски Johnnie Walker Black Label','ar'=>'ويسكي جوني ووكر أسود'],
        'price' => 0.00,
    ],
    [
        'title' => ["tr"=>"Ballantine's Viski","en"=>"Ballantine's Whisky","de"=>"Ballantine's Whisky","ru"=>"Виски Ballantine's","ar"=>"ويسكي بالانتينز"],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'Chivas Regal 12 Viski','en'=>'Chivas Regal 12 Whisky','de'=>'Chivas Regal 12 Whisky','ru'=>'Виски Chivas Regal 12','ar'=>'ويسكي شيفاس ريغال 12'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'Dimple Viski','en'=>'Dimple Whisky','de'=>'Dimple Whisky','ru'=>'Виски Dimple','ar'=>'ويسكي ديمبل'],
        'price' => 0.00,
    ],
    [
        'title' => ["tr"=>"Jack Daniel's Viski","en"=>"Jack Daniel's Whiskey","de"=>"Jack Daniel's Whiskey","ru"=>"Виски Jack Daniel's","ar"=>"ويسكي جاك دانيالز"],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'Jim Beam Viski','en'=>'Jim Beam Bourbon Whiskey','de'=>'Jim Beam Bourbon Whiskey','ru'=>'Виски Jim Beam','ar'=>'ويسكي جيم بيم'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'Jameson İrlanda Viskisi','en'=>'Jameson Irish Whiskey','de'=>'Jameson Irish Whiskey','ru'=>'Виски Jameson Irish','ar'=>'ويسكي جيمسون الأيرلندي'],
        'price' => 0.00,
    ],

    // ─── RAKI ────────────────────────────────────────────────────────────────
    [
        'title' => ['tr'=>'Yeni Rakı','en'=>'Yeni Raki','de'=>'Yeni Raki','ru'=>'Ракы Yeni','ar'=>'عرق يني راكي'],
        'price' => 0.00,
    ],
    [
        'title' => ['tr'=>'Tekirdağ Gold Rakı','en'=>'Tekirdag Gold Raki','de'=>'Tekirdag Gold Raki','ru'=>'Ракы Tekirdağ Gold','ar'=>'عرق تيكيرداغ جولد'],
        'price' => 0.00,
    ],
];

$sort = 1;
$created = 0;

foreach ($products as $data) {
    $p = FoodProduct::create([
        'branch_id'        => $branchId,
        'food_category_id' => $categoryId,
        'title'            => $data['title'],
        'description'      => [],
        'price'            => $data['price'],
        'sort_order'       => $sort++,
        'is_active'        => true,
        'badges'           => [],
        'allergens'        => [],
    ]);
    echo "[{$p->id}] " . $p->getTitle() . " — " . number_format($p->price, 2) . " EUR" . PHP_EOL;
    $created++;
}

echo PHP_EOL . "✓ {$created} ürün eklendi." . PHP_EOL;
