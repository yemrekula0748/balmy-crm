<?php
/**
 * Balmy Beach QR Menu (ID=9) — Ürün Ekleme Scripti
 * Kategoriler: 27=Başlangıçlar, 28=Ara Sıcaklar, 29=Salatalar, 30=Ana Yemekler, 31=Tatlılar
 * branch_id = 1
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodProduct;
use App\Models\QrMenuItem;

$branchId = 1;
$created = 0;

/**
 * Her ürün için:
 *  - FoodProduct oluştur (allergen + nutrition burada)
 *  - QrMenuItem oluştur ve food_product_id ile bağla
 */
$items = [

    /* ─────────────────────────────────────────────
       KATEGORİ 27 — BAŞLANGIÇLAR
    ───────────────────────────────────────────── */
    [
        'category_id' => 27,
        'price'       => 6.00,
        'sort_order'  => 1,
        'badges'      => ['Önerilen'],
        'title'       => [
            'tr' => 'Somon Füme Sandviç',
            'en' => 'Smoked Salmon Sandwich',
            'de' => 'Geräucherter Lachs Sandwich',
            'ru' => 'Сэндвич с копчёным лососем',
            'ar' => 'ساندويش السلمون المدخن',
        ],
        'description' => [
            'tr' => 'Roka · Somon Füme · Kırmızı Soğan · Patates Tava',
            'en' => 'Arugula · Smoked Salmon · Red Onion · Pan-Fried Potatoes',
            'de' => 'Rucola · Geräucherter Lachs · Rote Zwiebel · Bratkartoffeln',
            'ru' => 'Руккола · Копчёный лосось · Красный лук · Жареный картофель',
            'ar' => 'جرجير · سلمون مدخن · بصل أحمر · بطاطس مقلية',
        ],
        'allergens'  => ['gluten', 'balik'],
        'calories'   => 345.00,
        'protein'    => 20.00,
        'carbs'      => 34.00,
        'fat'        => 11.00,
    ],
    [
        'category_id' => 27,
        'price'       => 10.00,
        'sort_order'  => 2,
        'badges'      => [],
        'title'       => [
            'tr' => 'Roast Beef Sandviç',
            'en' => 'Roast Beef Sandwich',
            'de' => 'Roast Beef Sandwich',
            'ru' => 'Сэндвич с ростбифом',
            'ar' => 'ساندويش الروستبيف',
        ],
        'description' => [
            'tr' => 'Baget Ekmeği · Kornişon Turşu · Hardal · Roka · Patates Tava',
            'en' => 'Baguette · Gherkin Pickles · Mustard · Arugula · Pan-Fried Potatoes',
            'de' => 'Baguette · Gewürzgurken · Senf · Rucola · Bratkartoffeln',
            'ru' => 'Багет · Маринованные огурцы · Горчица · Руккола · Жареный картофель',
            'ar' => 'خبز باغيت · مخلل · خردل · جرجير · بطاطس مقلية',
        ],
        'allergens'  => ['gluten', 'hardal'],
        'calories'   => 480.00,
        'protein'    => 28.00,
        'carbs'      => 42.00,
        'fat'        => 18.00,
    ],
    [
        'category_id' => 27,
        'price'       => 6.00,
        'sort_order'  => 3,
        'badges'      => [],
        'title'       => [
            'tr' => 'Tavuk Füme Göğsü Sandviç',
            'en' => 'Smoked Chicken Breast Sandwich',
            'de' => 'Geräucherte Hähnchenbrust Sandwich',
            'ru' => 'Сэндвич с копчёной куриной грудкой',
            'ar' => 'ساندويش صدر الدجاج المدخن',
        ],
        'description' => [
            'tr' => 'Kızarmış Ekmek · Tavuk Füme · Marul · Domates · Cheddar · Mayonez · Patates Tava · Turşu',
            'en' => 'Toasted Bread · Smoked Chicken · Lettuce · Tomato · Cheddar · Mayonnaise · Pan-Fried Potatoes · Pickles',
            'de' => 'Geröstetes Brot · Geräuchertes Huhn · Salat · Tomate · Cheddar · Mayonnaise · Bratkartoffeln · Gewürzgurken',
            'ru' => 'Тост · Копчёная курица · Салат · Помидор · Чеддер · Майонез · Жареный картофель · Маринованные огурцы',
            'ar' => 'خبز محمص · دجاج مدخن · خس · طماطم · جبنة شيدر · مايونيز · بطاطس مقلية · مخلل',
        ],
        'allergens'  => ['gluten', 'yumurta', 'sut'],
        'calories'   => 420.00,
        'protein'    => 26.00,
        'carbs'      => 38.00,
        'fat'        => 16.00,
    ],

    /* ─────────────────────────────────────────────
       KATEGORİ 28 — ARA SICAKLAR
    ───────────────────────────────────────────── */
    [
        'category_id' => 28,
        'price'       => 8.00,
        'sort_order'  => 1,
        'badges'      => ['Vejeteryan'],
        'title'       => [
            'tr' => 'Kremalı Mantarlı Penne',
            'en' => 'Creamy Mushroom Penne',
            'de' => 'Penne mit cremiger Pilzsauce',
            'ru' => 'Пенне со сливочным соусом и грибами',
            'ar' => 'باستا بيني بالفطر والكريمة',
        ],
        'description' => [
            'tr' => 'Taze Mantar · Parmesan · Kremalı Sos',
            'en' => 'Fresh Mushroom · Parmesan · Cream Sauce',
            'de' => 'Frische Champignons · Parmesan · Sahnesauce',
            'ru' => 'Свежие грибы · Пармезан · Сливочный соус',
            'ar' => 'فطر طازج · بارميزان · صلصة كريمة',
        ],
        'allergens'  => ['gluten', 'sut'],
        'calories'   => 520.00,
        'protein'    => 16.00,
        'carbs'      => 65.00,
        'fat'        => 22.00,
    ],
    [
        'category_id' => 28,
        'price'       => 8.00,
        'sort_order'  => 2,
        'badges'      => ['Vegan', 'Vejeteryan'],
        'title'       => [
            'tr' => 'Pomodoro & Fesleğen Spagetti',
            'en' => 'Pomodoro & Basil Spaghetti',
            'de' => 'Pomodoro & Basilikum Spaghetti',
            'ru' => 'Спагетти помодоро с базиликом',
            'ar' => 'سباغيتي بومودورو والريحان',
        ],
        'description' => [
            'tr' => 'Domates Sos · Taze Fesleğen · Zeytinyağı',
            'en' => 'Tomato Sauce · Fresh Basil · Olive Oil',
            'de' => 'Tomatensauce · Frisches Basilikum · Olivenöl',
            'ru' => 'Томатный соус · Свежий базилик · Оливковое масло',
            'ar' => 'صلصة طماطم · ريحان طازج · زيت زيتون',
        ],
        'allergens'  => ['gluten'],
        'calories'   => 460.00,
        'protein'    => 12.00,
        'carbs'      => 72.00,
        'fat'        => 14.00,
    ],
    [
        'category_id' => 28,
        'price'       => 12.00,
        'sort_order'  => 3,
        'badges'      => ['Vejeteryan'],
        'title'       => [
            'tr' => 'Dört Peynir Tortelini',
            'en' => 'Four Cheese Tortellini',
            'de' => 'Vier-Käse-Tortellini',
            'ru' => 'Тортеллини четыре сыра',
            'ar' => 'تورتيليني أربعة أجبان',
        ],
        'description' => [
            'tr' => 'Mozzarella · Ricotta · Parmesan · Gorgonzola · Kremalı Sos',
            'en' => 'Mozzarella · Ricotta · Parmesan · Gorgonzola · Cream Sauce',
            'de' => 'Mozzarella · Ricotta · Parmesan · Gorgonzola · Sahnesauce',
            'ru' => 'Моцарелла · Рикотта · Пармезан · Горгонзола · Сливочный соус',
            'ar' => 'موزاريلا · ريكوتا · بارميزان · جورجونزولا · صلصة كريمة',
        ],
        'allergens'  => ['gluten', 'sut', 'yumurta'],
        'calories'   => 580.00,
        'protein'    => 22.00,
        'carbs'      => 60.00,
        'fat'        => 28.00,
    ],
    [
        'category_id' => 28,
        'price'       => 6.00,
        'sort_order'  => 4,
        'badges'      => [],
        'title'       => [
            'tr' => 'Tavuk Quesadilla',
            'en' => 'Chicken Quesadilla',
            'de' => 'Hähnchen Quesadilla',
            'ru' => 'Куриная кесадилья',
            'ar' => 'كيساديا الدجاج',
        ],
        'description' => [
            'tr' => 'Tatlı-Acı Sos · Soya Sos · Cheddar Peyniri',
            'en' => 'Sweet-Chili Sauce · Soy Sauce · Cheddar Cheese',
            'de' => 'Süß-scharfe Sauce · Sojasoße · Cheddar-Käse',
            'ru' => 'Кисло-сладкий соус · Соевый соус · Сыр чеддер',
            'ar' => 'صلصة حلوة حارة · صلصة الصويا · جبنة شيدر',
        ],
        'allergens'  => ['gluten', 'soya', 'sut'],
        'calories'   => 440.00,
        'protein'    => 24.00,
        'carbs'      => 38.00,
        'fat'        => 20.00,
    ],
    [
        'category_id' => 28,
        'price'       => 12.00,
        'sort_order'  => 5,
        'badges'      => ['Popüler'],
        'title'       => [
            'tr' => 'Hamburger / Cheeseburger',
            'en' => 'Hamburger / Cheeseburger',
            'de' => 'Hamburger / Cheeseburger',
            'ru' => 'Гамбургер / Чизбургер',
            'ar' => 'برغر / تشيزبرغر',
        ],
        'description' => [
            'tr' => 'Izgara Köfte · Tereyağlı Burger Ekmeği · Patates Tava',
            'en' => 'Grilled Beef Patty · Buttered Burger Bun · Pan-Fried Potatoes',
            'de' => 'Gegrilltes Fleischpatty · Gebutterte Burgerbrötchen · Bratkartoffeln',
            'ru' => 'Котлета говяжья гриль · Булочка с маслом · Жареный картофель',
            'ar' => 'كوفتة مشوية · خبز برغر بالزبدة · بطاطس مقلية',
        ],
        'allergens'  => ['gluten', 'sut', 'yumurta'],
        'calories'   => 650.00,
        'protein'    => 32.00,
        'carbs'      => 52.00,
        'fat'        => 32.00,
    ],
    [
        'category_id' => 28,
        'price'       => 6.00,
        'sort_order'  => 6,
        'badges'      => [],
        'title'       => [
            'tr' => 'Karışık Tost',
            'en' => 'Mixed Toast',
            'de' => 'Gemischter Toast',
            'ru' => 'Смешанный тост',
            'ar' => 'توست مشكل',
        ],
        'description' => [
            'tr' => 'Salça · Kaşar · Dana Sucuk · Patates Tava',
            'en' => 'Tomato Paste · Kashar Cheese · Beef Sausage · Pan-Fried Potatoes',
            'de' => 'Tomatenmark · Kaşar-Käse · Rindswurst · Bratkartoffeln',
            'ru' => 'Томатная паста · Сыр кашар · Говяжья колбаса · Жареный картофель',
            'ar' => 'معجون طماطم · جبنة كاشار · سجق بقري · بطاطس مقلية',
        ],
        'allergens'  => ['gluten', 'sut'],
        'calories'   => 480.00,
        'protein'    => 22.00,
        'carbs'      => 45.00,
        'fat'        => 24.00,
    ],
    [
        'category_id' => 28,
        'price'       => 5.00,
        'sort_order'  => 7,
        'badges'      => ['Vejeteryan'],
        'title'       => [
            'tr' => 'Kaşarlı Tost',
            'en' => 'Cheese Toast',
            'de' => 'Käse Toast',
            'ru' => 'Тост с сыром',
            'ar' => 'توست بالجبنة',
        ],
        'description' => [
            'tr' => 'Salça · Kaşar · Patates Tava',
            'en' => 'Tomato Paste · Kashar Cheese · Pan-Fried Potatoes',
            'de' => 'Tomatenmark · Kaşar-Käse · Bratkartoffeln',
            'ru' => 'Томатная паста · Сыр кашар · Жареный картофель',
            'ar' => 'معجون طماطم · جبنة كاشار · بطاطس مقلية',
        ],
        'allergens'  => ['gluten', 'sut'],
        'calories'   => 380.00,
        'protein'    => 14.00,
        'carbs'      => 42.00,
        'fat'        => 18.00,
    ],

    /* ─────────────────────────────────────────────
       KATEGORİ 29 — SALATALAR
    ───────────────────────────────────────────── */
    [
        'category_id' => 29,
        'price'       => 6.00,
        'sort_order'  => 1,
        'badges'      => ['Vegan', 'Vejeteryan'],
        'title'       => [
            'tr' => 'Roka & Avokado Salatası',
            'en' => 'Arugula & Avocado Salad',
            'de' => 'Rucola & Avocado Salat',
            'ru' => 'Салат из рукколы и авокадо',
            'ar' => 'سلطة الجرجير والأفوكادو',
        ],
        'description' => [
            'tr' => 'Taze Roka · Avokado · Kavrulmuş Badem · Narenciye Sos',
            'en' => 'Fresh Arugula · Avocado · Toasted Almonds · Citrus Dressing',
            'de' => 'Frischer Rucola · Avocado · Geröstete Mandeln · Zitrus-Dressing',
            'ru' => 'Свежая руккола · Авокадо · Жареный миндаль · Цитрусовая заправка',
            'ar' => 'جرجير طازج · أفوكادو · لوز محمص · صلصة الحمضيات',
        ],
        'allergens'  => ['kuruyemis'],
        'calories'   => 280.00,
        'protein'    => 6.00,
        'carbs'      => 14.00,
        'fat'        => 22.00,
    ],
    [
        'category_id' => 29,
        'price'       => 6.00,
        'sort_order'  => 2,
        'badges'      => ['Vegan', 'Vejeteryan'],
        'title'       => [
            'tr' => 'Akdeniz Salatası',
            'en' => 'Mediterranean Salad',
            'de' => 'Mediterraner Salat',
            'ru' => 'Средиземноморский салат',
            'ar' => 'سلطة متوسطية',
        ],
        'description' => [
            'tr' => 'Salatalık · Domates · Zeytin · Kırmızı Soğan · Limon-Zeytinyağı Sos',
            'en' => 'Cucumber · Tomato · Olives · Red Onion · Lemon-Olive Oil Dressing',
            'de' => 'Gurke · Tomate · Oliven · Rote Zwiebel · Zitronen-Olivenöl-Dressing',
            'ru' => 'Огурец · Помидор · Оливки · Красный лук · Лимонно-оливковая заправка',
            'ar' => 'خيار · طماطم · زيتون · بصل أحمر · صلصة الليمون وزيت الزيتون',
        ],
        'allergens'  => [],
        'calories'   => 180.00,
        'protein'    => 4.00,
        'carbs'      => 16.00,
        'fat'        => 12.00,
    ],

    /* ─────────────────────────────────────────────
       KATEGORİ 30 — ANA YEMEKLER
    ───────────────────────────────────────────── */
    [
        'category_id' => 30,
        'price'       => 12.00,
        'sort_order'  => 1,
        'badges'      => ['Önerilen'],
        'title'       => [
            'tr' => 'Izgara Tavuk Göğsü',
            'en' => 'Grilled Chicken Breast',
            'de' => 'Gegrillte Hähnchenbrust',
            'ru' => 'Куриная грудка гриль',
            'ar' => 'صدر دجاج مشوي',
        ],
        'description' => [
            'tr' => 'Baharatlı Yoğurt Sos · Sote Sebzeler · Hafif Tahin',
            'en' => 'Spiced Yogurt Sauce · Sautéed Vegetables · Light Tahini',
            'de' => 'Gewürzte Joghurtsauce · Sautiertes Gemüse · Heller Tahini',
            'ru' => 'Пряный соус из йогурта · Тушёные овощи · Лёгкий тахини',
            'ar' => 'صلصة زبادي بالبهارات · خضروات مقلية · طحينة خفيفة',
        ],
        'allergens'  => ['sut', 'susam'],
        'calories'   => 380.00,
        'protein'    => 42.00,
        'carbs'      => 18.00,
        'fat'        => 14.00,
    ],
    [
        'category_id' => 30,
        'price'       => 15.00,
        'sort_order'  => 2,
        'badges'      => ['Önerilen'],
        'title'       => [
            'tr' => 'Izgara Fileto Levrek',
            'en' => 'Grilled Sea Bass Fillet',
            'de' => 'Gegrilltes Wolfsbarschfilet',
            'ru' => 'Филе морского окуня гриль',
            'ar' => 'فيليه سمك قاروص مشوي',
        ],
        'description' => [
            'tr' => 'Limon-Tereyağlı Sos · Mevsim Sebzeleri',
            'en' => 'Lemon-Butter Sauce · Seasonal Vegetables',
            'de' => 'Zitronen-Butter-Sauce · Saisongemüse',
            'ru' => 'Лимонно-сливочный соус · Сезонные овощи',
            'ar' => 'صلصة زبدة الليمون · خضروات موسمية',
        ],
        'allergens'  => ['balik', 'sut'],
        'calories'   => 320.00,
        'protein'    => 38.00,
        'carbs'      => 8.00,
        'fat'        => 16.00,
    ],
    [
        'category_id' => 30,
        'price'       => 30.00,
        'sort_order'  => 3,
        'badges'      => ['Önerilen', 'Popüler'],
        'title'       => [
            'tr' => 'Dana Bonfile',
            'en' => 'Beef Tenderloin',
            'de' => 'Rinderfilet',
            'ru' => 'Говяжья вырезка',
            'ar' => 'فيليه لحم بقري',
        ],
        'description' => [
            'tr' => 'Izgara Dana Bonfile · Patates Sote · Mevsim Sebzeleri',
            'en' => 'Grilled Beef Tenderloin · Sautéed Potatoes · Seasonal Vegetables',
            'de' => 'Gegrilltes Rinderfilet · Sautierte Kartoffeln · Saisongemüse',
            'ru' => 'Говяжья вырезка гриль · Тушёный картофель · Сезонные овощи',
            'ar' => 'فيليه بقري مشوي · بطاطس سوتيه · خضروات موسمية',
        ],
        'allergens'  => [],
        'calories'   => 580.00,
        'protein'    => 52.00,
        'carbs'      => 22.00,
        'fat'        => 28.00,
    ],

    /* ─────────────────────────────────────────────
       KATEGORİ 31 — TATLILAR
    ───────────────────────────────────────────── */
    [
        'category_id' => 31,
        'price'       => 5.00,
        'sort_order'  => 1,
        'badges'      => [],
        'title'       => [
            'tr' => 'Fırınlanmış Sütlaç',
            'en' => 'Baked Rice Pudding',
            'de' => 'Gebackener Milchreis',
            'ru' => 'Запечённый рисовый пудинг',
            'ar' => 'أرز حليب مخبوز',
        ],
        'description' => [
            'tr' => 'Geleneksel Türk Tatlısı · Vanilyalı Dondurma',
            'en' => 'Traditional Turkish Dessert · Vanilla Ice Cream',
            'de' => 'Traditionelles türkisches Dessert · Vanilleeis',
            'ru' => 'Традиционный турецкий десерт · Ванильное мороженое',
            'ar' => 'حلوى تركية تقليدية · آيس كريم فانيليا',
        ],
        'allergens'  => ['sut', 'yumurta'],
        'calories'   => 380.00,
        'protein'    => 8.00,
        'carbs'      => 58.00,
        'fat'        => 14.00,
    ],
    [
        'category_id' => 31,
        'price'       => 6.00,
        'sort_order'  => 2,
        'badges'      => ['Vegan', 'Vejeteryan'],
        'title'       => [
            'tr' => 'Meyve Tabağı',
            'en' => 'Fruit Platter',
            'de' => 'Obstplatte',
            'ru' => 'Фруктовая тарелка',
            'ar' => 'طبق فواكه',
        ],
        'description' => [
            'tr' => 'Günün Taze Meyveleri',
            'en' => "Today's Fresh Fruits",
            'de' => 'Frische Früchte des Tages',
            'ru' => 'Свежие фрукты дня',
            'ar' => 'فواكه طازجة اليوم',
        ],
        'allergens'  => [],
        'calories'   => 140.00,
        'protein'    => 2.00,
        'carbs'      => 34.00,
        'fat'        => 1.00,
    ],
    [
        'category_id' => 31,
        'price'       => 6.00,
        'sort_order'  => 3,
        'badges'      => [],
        'title'       => [
            'tr' => 'Ceviz Brownie',
            'en' => 'Walnut Brownie',
            'de' => 'Walnuss-Brownie',
            'ru' => 'Брауни с грецкими орехами',
            'ar' => 'براوني الجوز',
        ],
        'description' => [
            'tr' => 'Çikolatalı Brownie · Ceviz · Vanilyalı Dondurma',
            'en' => 'Chocolate Brownie · Walnuts · Vanilla Ice Cream',
            'de' => 'Schokoladen-Brownie · Walnüsse · Vanilleeis',
            'ru' => 'Шоколадный брауни · Грецкие орехи · Ванильное мороженое',
            'ar' => 'براوني شوكولاتة · جوز · آيس كريم فانيليا',
        ],
        'allergens'  => ['gluten', 'kuruyemis', 'sut', 'yumurta'],
        'calories'   => 450.00,
        'protein'    => 6.00,
        'carbs'      => 52.00,
        'fat'        => 24.00,
    ],
];

echo "Toplam " . count($items) . " ürün eklenecek..." . PHP_EOL;

foreach ($items as $data) {
    // 1) FoodProduct oluştur
    $fp = FoodProduct::create([
        'branch_id'      => $branchId,
        'food_category_id' => null,
        'printer_id'     => null,
        'title'          => $data['title'],
        'description'    => $data['description'],
        'price'          => $data['price'],
        'image'          => null,
        'badges'         => $data['badges'] ?: null,
        'allergens'      => !empty($data['allergens']) ? $data['allergens'] : null,
        'ingredients'    => null,
        'options'        => null,
        'calories'       => $data['calories'],
        'protein'        => $data['protein'],
        'carbs'          => $data['carbs'],
        'fat'            => $data['fat'],
        'is_active'      => true,
        'sort_order'     => $data['sort_order'],
    ]);

    // 2) QrMenuItem oluştur ve food_product'a bağla
    $item = QrMenuItem::create([
        'category_id'     => $data['category_id'],
        'food_product_id' => $fp->id,
        'title'           => $data['title'],
        'description'     => $data['description'],
        'price'           => $data['price'],
        'price_override'  => null,
        'image'           => null,
        'is_active'       => true,
        'is_featured'     => false,
        'badges'          => $data['badges'] ?: null,
        'sort_order'      => $data['sort_order'],
    ]);

    $created++;
    echo "  ✓ [{$item->id}] " . $data['title']['tr'] . " (FoodProduct #{$fp->id})" . PHP_EOL;
}

echo PHP_EOL . "Tamamlandı: {$created} ürün başarıyla eklendi." . PHP_EOL;
