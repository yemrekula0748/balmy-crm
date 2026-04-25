<?php
/**
 * Balmy Foresta (branch_id=2) — İçecek Menüsü Toplu Ekleme
 * 1) Food library kategorileri (içecek grupları)
 * 2) Food library ürünleri
 * 3) QR Menü 18 → şarap kategorileri eklenir (Kırmızı, Beyaz, Pembe, Şampanya)
 * 4) Tüm ürünler QR menü 18'e bağlanır
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodCategory;
use App\Models\FoodProduct;
use App\Models\QrMenuCategory;
use App\Models\QrMenuItem;

$branchId = 2;
$qrMenuId = 18;
$sharedImage = 'food_library/h4bEIzvF1AGLfDJmlfJujj6KZ6T4TD33hk6toadT.png';

// ─────────────────────────────────────────────────────────────────
// ADIM 1: Food Library kategorileri (yeni olanları ekle)
// ─────────────────────────────────────────────────────────────────
$libCatDefs = [
    ['tr'=>'Kahveler',           'en'=>'Coffees',              'de'=>'Kaffees',                  'ru'=>'Кофе'],
    ['tr'=>'Çaylar',             'en'=>'Teas',                 'de'=>'Tees',                     'ru'=>'Чаи'],
    ['tr'=>'Su & Maden Suyu',    'en'=>'Water & Mineral Water','de'=>'Wasser & Mineralwasser',   'ru'=>'Вода и минеральная вода'],
    ['tr'=>'Gazsız İçecekler',   'en'=>'Still Beverages',      'de'=>'Stille Getränke',          'ru'=>'Негазированные напитки'],
    ['tr'=>'Gazlı İçecekler',    'en'=>'Sparkling Beverages',  'de'=>'Sprudelnde Getränke',      'ru'=>'Газированные напитки'],
    ['tr'=>'Biralar',            'en'=>'Beers',                'de'=>'Biere',                    'ru'=>'Пиво'],
    ['tr'=>'Rakı',               'en'=>'Raki',                 'de'=>'Raki',                     'ru'=>'Раки'],
    ['tr'=>'Votkalar',           'en'=>'Vodkas',               'de'=>'Wodkas',                   'ru'=>'Водки'],
    ['tr'=>'Viskiler',           'en'=>'Whiskies',             'de'=>'Whiskys',                  'ru'=>'Виски'],
    ['tr'=>'Cin',                'en'=>'Gin',                  'de'=>'Gin',                      'ru'=>'Джин'],
    ['tr'=>'Rom & Cachaça',      'en'=>'Rum & Cachaça',        'de'=>'Rum & Cachaça',            'ru'=>'Ром & Кашаса'],
    ['tr'=>'Tekilalar',          'en'=>'Tequilas',             'de'=>'Tequilas',                 'ru'=>'Текилы'],
    ['tr'=>'Konyak & Brendi',    'en'=>'Cognac & Brandy',      'de'=>'Cognac & Brandy',          'ru'=>'Коньяк & Бренди'],
    ['tr'=>'Likör',              'en'=>'Liqueur',              'de'=>'Likör',                    'ru'=>'Ликёр'],
    ['tr'=>'Vermut',             'en'=>'Vermouth',             'de'=>'Wermut',                   'ru'=>'Вермут'],
    ['tr'=>'Aperatif',           'en'=>'Aperitif',             'de'=>'Aperitif',                 'ru'=>'Аперитив'],
    ['tr'=>'Digestiv',           'en'=>'Digestif',             'de'=>'Digestif',                 'ru'=>'Дижестив'],
    ['tr'=>'Klasik Kokteyller',  'en'=>'Classic Cocktails',    'de'=>'Klassische Cocktails',     'ru'=>'Классические коктейли'],
    ['tr'=>'Yeni Nesil Kokteyller','en'=>'New Generation Cocktails','de'=>'Neue Cocktails',      'ru'=>'Коктейли нового поколения'],
    ['tr'=>'Alkolsüz Kokteyller','en'=>'Non-Alcoholic Cocktails','de'=>'Alkoholfreie Cocktails', 'ru'=>'Безалкогольные коктейли'],
    ['tr'=>'Kırmızı Şaraplar',   'en'=>'Red Wines',            'de'=>'Rotweine',                 'ru'=>'Красные вина'],
    ['tr'=>'Beyaz Şaraplar',     'en'=>'White Wines',          'de'=>'Weißweine',                'ru'=>'Белые вина'],
    ['tr'=>'Pembe Şaraplar',     'en'=>'Rosé Wines',           'de'=>'Roséweine',                'ru'=>'Розовые вина'],
    ['tr'=>'Şampanyalar',        'en'=>'Champagnes & Sparkling','de'=>'Champagner & Sekt',       'ru'=>'Шампанское и игристые'],
];

$libCatMap = []; // tr name => food_category id
$maxLibCatSort = FoodCategory::where('branch_id', $branchId)->max('sort_order') ?? 0;
$si = 1;
foreach ($libCatDefs as $def) {
    $existing = FoodCategory::where('branch_id', $branchId)
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title,'$.tr'))=?", [$def['tr']])
        ->first();
    if ($existing) {
        $libCatMap[$def['tr']] = $existing->id;
    } else {
        $cat = FoodCategory::create([
            'branch_id'  => $branchId,
            'title'      => $def,
            'sort_order' => $maxLibCatSort + $si++,
            'is_active'  => true,
        ]);
        $libCatMap[$def['tr']] = $cat->id;
        echo "LIB KATEGORİ EKLENDİ: {$def['tr']} (#{$cat->id})\n";
    }
}

// ─────────────────────────────────────────────────────────────────
// ADIM 2: QR Menü 18 — eksik şarap kategorilerini ekle
// ─────────────────────────────────────────────────────────────────
$qrNewCats = [
    ['tr'=>'Alkollü Sıcak İçecekler','en'=>'Hot Alcoholic Beverages','de'=>'Heiße alkoholische Getränke','ru'=>'Горячие алкогольные напитки'],
    ['tr'=>'Kırmızı Şaraplar',       'en'=>'Red Wines',              'de'=>'Rotweine',                   'ru'=>'Красные вина'],
    ['tr'=>'Beyaz Şaraplar',         'en'=>'White Wines',            'de'=>'Weißweine',                  'ru'=>'Белые вина'],
    ['tr'=>'Pembe Şaraplar',         'en'=>'Rosé Wines',             'de'=>'Roséweine',                  'ru'=>'Розовые вина'],
    ['tr'=>'Şampanyalar',            'en'=>'Champagnes & Sparkling', 'de'=>'Champagner & Sekt',          'ru'=>'Шампанское и игристые'],
];

$maxQrSort = QrMenuCategory::where('qr_menu_id', $qrMenuId)->max('sort_order') ?? 0;
$qrCatMap = [];
// load existing
foreach (QrMenuCategory::where('qr_menu_id', $qrMenuId)->get() as $c) {
    $qrCatMap[$c->getTitle('tr')] = $c->id;
}
$qi = 1;
foreach ($qrNewCats as $def) {
    if (isset($qrCatMap[$def['tr']])) continue;
    $cat = QrMenuCategory::create([
        'qr_menu_id' => $qrMenuId,
        'title'      => $def,
        'sort_order' => $maxQrSort + $qi++,
        'is_active'  => true,
    ]);
    $qrCatMap[$def['tr']] = $cat->id;
    echo "QR KATEGORİ EKLENDİ: {$def['tr']} (#{$cat->id})\n";
}
// reload all
foreach (QrMenuCategory::where('qr_menu_id', $qrMenuId)->get() as $c) {
    $qrCatMap[$c->getTitle('tr')] = $c->id;
}

// ─────────────────────────────────────────────────────────────────
// ADIM 3: Ürün listesi
// Her ürün: [libCatTr, qrCatTr, titleTr, titleEn, titleDe, titleRu, desc_tr]
// ─────────────────────────────────────────────────────────────────
$products = [
    // ── Kahveler ──
    ['Kahveler','Kahveler','Espresso','Espresso','Espresso','Эспрессо','Yoğun ve aromatik tek shot espresso','Dense aromatic single shot espresso','Dichter aromatischer Einzel-Espresso','Плотный ароматный одиночный эспрессо'],
    ['Kahveler','Kahveler','Americano','Americano','Americano','Американо','Espresso üzerine sıcak su eklenmiş yumuşak kahve','Espresso topped with hot water for a mild coffee','Espresso mit heißem Wasser für einen milden Kaffee','Эспрессо с горячей водой — мягкий кофе'],
    ['Kahveler','Kahveler','Cappuccino','Cappuccino','Cappuccino','Капучино','Espresso, buharlı süt ve süt köpüğü ile','Espresso with steamed milk and milk foam','Espresso mit Dampfmilch und Milchschaum','Эспрессо с паровым молоком и молочной пеной'],
    ['Kahveler','Kahveler','Cafe Latte','Cafe Latte','Café Latte','Кафе Латте','Espresso ile bol buharlı süt','Espresso with generous steamed milk','Espresso mit viel Dampfmilch','Эспрессо с большим количеством парового молока'],
    ['Kahveler','Kahveler','Türk Kahvesi','Turkish Coffee','Türkischer Kaffee','Турецкий кофе','Geleneksel yöntemle pişirilmiş Türk kahvesi','Turkish coffee brewed in the traditional way','Türkischer Kaffee, auf traditionelle Art zubereitet','Турецкий кофе, приготовленный по традиционному рецепту'],
    ['Kahveler','Kahveler','Sıcak Çikolata','Hot Chocolate','Heiße Schokolade','Горячий шоколад','Kremsi sıcak çikolata','Creamy hot chocolate','Cremige heiße Schokolade','Сливочный горячий шоколад'],
    // ── Çaylar ──
    ['Çaylar','Çaylar','Çay','Tea','Tee','Чай','Demlik çay','Pot tea','Teekanne','Чай из чайника'],
    ['Çaylar','Çaylar','Ihlamur','Linden Tea','Lindenblüten-Tee','Липовый чай','Taze ıhlamur çiçeğinden hazırlanmış bitki çayı','Herbal tea from fresh linden blossom','Kräutertee aus frischen Lindenblüten','Травяной чай из свежих цветов липы'],
    ['Çaylar','Çaylar','Adaçayı','Sage Tea','Salbeitee','Шалфейный чай','Kurutulmuş adaçayı yapraklarından demlenen bitki çayı','Herbal tea brewed from dried sage leaves','Kräutertee aus getrockneten Salbeiblättern','Травяной чай из сушёных листьев шалфея'],
    ['Çaylar','Çaylar','Nane Limon','Mint Lemon Tea','Minze-Zitronen-Tee','Мятно-лимонный чай','Taze nane ve limon ile hazırlanan ferahlatıcı çay','Refreshing tea with fresh mint and lemon','Erfrischender Tee mit frischer Minze und Zitrone','Освежающий чай со свежей мятой и лимоном'],
    ['Çaylar','Çaylar','Kuşburnu','Rosehip Tea','Hagebutttee','Чай из шиповника','C vitamini bakımından zengin kuşburnu çayı','Rosehip tea rich in vitamin C','Hagebutttee, reich an Vitamin C','Чай из шиповника, богатый витамином C'],
    ['Çaylar','Çaylar','Papatya','Chamomile Tea','Kamillentee','Ромашковый чай','Sakinleştirici papatya çayı','Calming chamomile tea','Beruhigender Kamillentee','Успокаивающий ромашковый чай'],
    ['Çaylar','Çaylar','Yeşil Çay','Green Tea','Grüner Tee','Зелёный чай','Antioksidan zengini yeşil çay','Antioxidant-rich green tea','Antioxidantienreicher grüner Tee','Зелёный чай, богатый антиоксидантами'],
    // ── Su & Maden Suyu ──
    ['Su & Maden Suyu','Su & Maden Suyu','Su','Water','Wasser','Вода','Doğal kaynak suyu','Natural spring water','Natürliches Quellwasser','Природная родниковая вода'],
    ['Su & Maden Suyu','Su & Maden Suyu','Soda','Soda Water','Sodawasser','Содовая','Gazlı soda suyu','Sparkling soda water','Sprudelndes Sodawasser','Газированная содовая вода'],
    ['Su & Maden Suyu','Su & Maden Suyu','Meyveli Soda','Flavored Soda','Fruchtsoda','Газировка с фруктовым вкусом','Meyve aromalı soda suyu','Fruit flavored soda water','Fruchtig aromatisiertes Sodawasser','Содовая с фруктовым ароматом'],
    // ── Gazsız İçecekler ──
    ['Gazsız İçecekler','Gazsız İçecekler','Ayran','Ayran','Ayran','Айран','Soğuk ve ferahlatıcı geleneksel Türk içeceği','Cold and refreshing traditional Turkish drink','Kaltes erfrischendes traditionelles türkisches Getränk','Холодный освежающий традиционный турецкий напиток'],
    ['Gazsız İçecekler','Gazsız İçecekler','Limonata','Lemonade','Limonade','Лимонад','Taze sıkılmış limon ile ev yapımı limonata','Homemade lemonade with freshly squeezed lemon','Hausgemachte Limonade mit frisch gepresstem Zitronensaft','Домашний лимонад из свежевыжатого лимона'],
    ['Gazsız İçecekler','Gazsız İçecekler','Şalgam','Turnip Juice','Steckrübensaft','Сок из репы','Fermente edilmiş şalgam suyu, geleneksel Türk içeceği','Fermented turnip juice, traditional Turkish drink','Fermentierter Steckrübensaft, traditionelles türkisches Getränk','Ферментированный сок репы, традиционный турецкий напиток'],
    ['Gazsız İçecekler','Gazsız İçecekler','Portakal Suyu','Orange Juice','Orangensaft','Апельсиновый сок','Taze sıkılmış portakal suyu','Freshly squeezed orange juice','Frisch gepresster Orangensaft','Свежевыжатый апельсиновый сок'],
    ['Gazsız İçecekler','Gazsız İçecekler','Vişne Suyu','Sour Cherry Juice','Sauerkirschsaft','Сок из вишни','Taze vişne suyu','Fresh sour cherry juice','Frischer Sauerkirschsaft','Свежий вишнёвый сок'],
    ['Gazsız İçecekler','Gazsız İçecekler','Şeftali Suyu','Peach Juice','Pfirsichsaft','Персиковый сок','Taze şeftali suyu','Fresh peach juice','Frischer Pfirsichsaft','Свежий персиковый сок'],
    ['Gazsız İçecekler','Gazsız İçecekler','Elma Suyu','Apple Juice','Apfelsaft','Яблочный сок','Taze elma suyu','Fresh apple juice','Frischer Apfelsaft','Свежий яблочный сок'],
    ['Gazsız İçecekler','Gazsız İçecekler','Ananas Suyu','Pineapple Juice','Ananassaft','Ананасовый сок','Taze ananas suyu','Fresh pineapple juice','Frischer Ananassaft','Свежий ананасовый сок'],
    ['Gazsız İçecekler','Gazsız İçecekler','Domates Suyu','Tomato Juice','Tomatensaft','Томатный сок','Taze domates suyu','Fresh tomato juice','Frischer Tomatensaft','Свежий томатный сок'],
    // ── Gazlı İçecekler ──
    ['Gazlı İçecekler','Gazlı İçecekler','Tonik','Tonic Water','Tonic Water','Тоник','Ferahlatıcı tonik suyu','Refreshing tonic water','Erfrischendes Tonic Water','Освежающая тоник-вода'],
    ['Gazlı İçecekler','Gazlı İçecekler','Bitter Lemon','Bitter Lemon','Bitter Lemon','Биттер Лемон','Limon aromalı hafif acı gazlı içecek','Slightly bitter lemon flavoured sparkling drink','Leicht bitteres Zitronengetränk mit Kohlensäure','Слегка горьковатый лимонный газированный напиток'],
    ['Gazlı İçecekler','Gazlı İçecekler','Coca-Cola','Coca-Cola','Coca-Cola','Кока-Кола','Klasik Coca-Cola','Classic Coca-Cola','Klassische Coca-Cola','Классическая Кока-Кола'],
    ['Gazlı İçecekler','Gazlı İçecekler','Fanta','Fanta','Fanta','Фанта','Portakal aromalı gazlı içecek','Orange flavoured sparkling drink','Orangenlimonade mit Kohlensäure','Газированный напиток с апельсиновым вкусом'],
    ['Gazlı İçecekler','Gazlı İçecekler','Sprite','Sprite','Sprite','Спрайт','Limon-misket limonu aromalı gazlı içecek','Lemon-lime flavoured sparkling drink','Zitronen-Limetten-Limonade mit Kohlensäure','Газированный напиток со вкусом лимона и лайма'],
    ['Gazlı İçecekler','Gazlı İçecekler','Coca-Cola Zero','Coca-Cola Zero','Coca-Cola Zero','Кока-Кола Зеро','Şekersiz Coca-Cola','Sugar-free Coca-Cola','Zuckerfreie Coca-Cola','Кока-Кола без сахара'],
    ['Gazlı İçecekler','Gazlı İçecekler','Ice Tea','Ice Tea','Eistee','Холодный чай','Soğuk çay','Cold tea','Kalter Tee','Холодный чай'],
    ['Gazlı İçecekler','Gazlı İçecekler','Enerji İçeceği','Energy Drink','Energydrink','Энергетический напиток','Kafeinli enerji içeceği','Caffeinated energy drink','Koffeinhaltiges Energydrink','Кофеинсодержащий энергетический напиток'],
    // ── Biralar ──
    ['Biralar','Biralar','Fıçı Bira','Draft Beer','Fassbier','Разливное пиво','Soğuk fıçı birası','Cold draft beer','Kaltes Fassbier','Холодное разливное пиво'],
    ['Biralar','Biralar','Tuborg Şişe Bira','Tuborg Bottled Beer','Tuborg Flaschenbier','Туборг бутылочное пиво','Tuborg şişe birası','Tuborg bottled beer','Tuborg Flaschenbier','Бутылочное пиво Туборг'],
    ['Biralar','Biralar','Miller','Miller','Miller','Миллер','Miller şişe birası','Miller bottled beer','Miller Flaschenbier','Пиво Miller в бутылке'],
    // ── Rakı ──
    ['Rakı','Rakı','Yeni Rakı','Yeni Raki','Yeni Raki','Ени Раки','Türkiye\'nin klasik anasonlu içkisi','Turkey\'s classic anise spirit','Türkischer Klassiker mit Anisgeschmack','Классический турецкий анисовый напиток'],
    ['Rakı','Rakı','Efe Rakı Yaş Üzüm','Efe Raki Fresh Grape','Efe Raki Frischtraube','Эфе Раки из свежего винограда','Taze üzümden üretilmiş Efe rakı','Efe raki made from fresh grapes','Efe Raki aus frischen Trauben hergestellt','Раки Эфе из свежего винограда'],
    ['Rakı','Rakı','Efe Rakı Gold','Efe Raki Gold','Efe Raki Gold','Эфе Раки Голд','Efe\'nin prestij serisi rakısı','Efe\'s prestige series raki','Efe\'s Prestige-Serie Raki','Престижная серия раки Эфе'],
    // ── Votkalar ──
    ['Votkalar','Votkalar','Smirnoff','Smirnoff','Smirnoff','Смирнофф','Dünyaca ünlü Rus votka markası','World-famous Russian vodka brand','Weltbekannte russische Wodkamarke','Всемирно известная российская водка'],
    ['Votkalar','Votkalar','Absolut','Absolut','Absolut','Абсолют','İsveç\'in ikonik vodka markası','Sweden\'s iconic vodka brand','Schwedische Ikonen-Wodkamarke','Икономическая шведская водка'],
    ['Votkalar','Votkalar','Gilbey\'s','Gilbey\'s','Gilbey\'s','Гилбис','Gilbey\'s votka','Gilbey\'s vodka','Gilbey\'s Wodka','Водка Джилбис'],
    ['Votkalar','Votkalar','İstanblue','Istanblue','Istanblue','Истанблу','Türk yapımı premium votka','Turkish-made premium vodka','Türkischer Premium-Wodka','Турецкая премиум-водка'],
    ['Votkalar','Votkalar','Absolut Elyx','Absolut Elyx','Absolut Elyx','Абсолют Элюкс','Bakır pot still ile üretilen single estate votka','Single estate vodka distilled in copper pot still','Single-Estate-Wodka in Kupferblasendestille','Водка single estate в медном кубе'],
    ['Votkalar','Votkalar','Belvedere Vodka','Belvedere Vodka','Belvedere Vodka','Водка Белведер','Polonya\'nın prestijli lüks votka markası','Poland\'s prestigious luxury vodka brand','Polens prestigiöse Luxus-Wodkamarke','Престижная польская водка класса люкс'],
    ['Votkalar','Votkalar','Beluga Vodka','Beluga Vodka','Beluga Vodka','Водка Белуга','Sibirya buğdayından üretilen Rus lüks votkası','Russian luxury vodka made from Siberian wheat','Russischer Luxuswodka aus sibirischem Weizen','Российская водка класса люкс из сибирской пшеницы'],
    // ── Viskiler ──
    ['Viskiler','Viskiler','J&B','J&B','J&B','J&B','Hafif ve meyvemsi İskoç viskey harmanı','Light and fruity Scotch whisky blend','Leichter und fruchtiger Scotch-Whisky-Blend','Лёгкий фруктовый купажированный шотландский виски'],
    ['Viskiler','Viskiler','Jim Beam','Jim Beam','Jim Beam','Джим Бим','Kentucky Straight Bourbon Whiskey','Kentucky Straight Bourbon Whiskey','Kentucky Straight Bourbon Whiskey','Кентукки Прямой Бурбон Виски'],
    ['Viskiler','Viskiler','Jim Beam Red Stag','Jim Beam Red Stag','Jim Beam Red Stag','Джим Бим Рэд Стэг','Kiraz aromalı Jim Beam bourbon','Cherry-infused Jim Beam bourbon','Jim Beam Bourbon mit Kirschgeschmack','Бурбон Джим Бим с вишнёвым вкусом'],
    ['Viskiler','Viskiler','Jim Beam Apple','Jim Beam Apple','Jim Beam Apple','Джим Бим Эппл','Elma aromalı Jim Beam bourbon','Apple-infused Jim Beam bourbon','Jim Beam Bourbon mit Apfelgeschmack','Бурбон Джим Бим с яблочным вкусом'],
    ['Viskiler','Viskiler','Jim Beam Honey','Jim Beam Honey','Jim Beam Honey','Джим Бим Хани','Bal aromalı Jim Beam bourbon','Honey-infused Jim Beam bourbon','Jim Beam Bourbon mit Honiggeschmack','Бурбон Джим Бим с медовым вкусом'],
    ['Viskiler','Viskiler','Johnnie Walker Red Label','Johnnie Walker Red Label','Johnnie Walker Red Label','Джонни Уокер Рэд Лейбл','Klasik İskoç viski harmanı','Classic Scotch whisky blend','Klassischer Scotch-Whisky-Blend','Классический купажированный шотландский виски'],
    ['Viskiler','Viskiler','Ballantine\'s','Ballantine\'s','Ballantine\'s','Баллантайнс','Yumuşak ve dengeli İskoç harman viski','Smooth and balanced Scotch blended whisky','Weicher und ausgewogener Scotch Blend Whisky','Мягкий сбалансированный купажный шотландский виски'],
    ['Viskiler','Viskiler','Chivas Regal 18 YO','Chivas Regal 18 YO — Blended Scotch','Chivas Regal 18 JO — Blended Scotch','Чивас Ригал 18 лет — Купажный шотландский','18 yıl dinlendirilmiş premium harman İskoç viskisi','Premium 18-year-aged blended Scotch whisky','Premium 18 Jahre gereifter Blend Scotch Whisky','Купажный шотландский виски выдержки 18 лет'],
    ['Viskiler','Viskiler','Chivas Regal 25 YO','Chivas Regal 25 YO — Blended Scotch','Chivas Regal 25 JO — Blended Scotch','Чивас Ригал 25 лет — Купажный шотландский','25 yıl dinlendirilmiş ultra premium harman İskoç viskisi','Ultra premium 25-year-aged blended Scotch whisky','Ultra Premium 25 Jahre gereifter Blend Scotch Whisky','Купажный шотландский виски выдержки 25 лет'],
    ['Viskiler','Viskiler','Macallan 12 YO','Macallan 12 YO — Highland Single Malt Scotch','Macallan 12 JO — Highland Single Malt Scotch','Макаллан 12 лет — Хайленд Сингл Молт','12 yıl dinlendirilmiş Highland bölgesi single malt viskisi','12-year-aged Highland single malt Scotch whisky','12 Jahre gereifter Highland Single Malt Scotch Whisky','Хайлэнд сингл молт виски выдержки 12 лет'],
    ['Viskiler','Viskiler','Glenfiddich 12 YO','Glenfiddich 12 YO — Single Malt Scotch','Glenfiddich 12 JO — Single Malt Scotch','Гленфиддик 12 лет — Сингл Молт Скотч','12 yıl dinlendirilmiş Glenfiddich single malt viskisi','Glenfiddich 12-year-aged single malt Scotch','Glenfiddich 12 Jahre Single Malt Scotch','Гленфиддик сингл молт виски выдержки 12 лет'],
    ['Viskiler','Viskiler','Bulleit Bourbon','Bulleit Bourbon — Kentucky Straight Bourbon','Bulleit Bourbon — Kentucky Straight Bourbon','Буллейт Бурбон — Кентукки Стрейт Бурбон','Yüksek çavdar oranıyla öne çıkan frontier tarzı bourbon','High-rye frontier-style Kentucky bourbon','Kentucky Bourbon mit hohem Roggenanteil','Кентуккский бурбон фронтьер-стиля с высоким содержанием ржи'],
    ['Viskiler','Viskiler','Johnnie Walker Blue Label','Johnnie Walker Blue Label — Blended Scotch','Johnnie Walker Blue Label — Blended Scotch','Джонни Уокер Блю Лейбл — Купажный шотландский','Nadir maltlardan oluşan efsane Johnnie Walker Blue Label','Legendary Johnnie Walker Blue Label from rare malts','Legendärer Johnnie Walker Blue Label aus seltenen Malts','Легендарный Джонни Уокер Блю Лейбл из редких солодов'],
    // ── Cin ──
    ['Cin','Cin','Gordon\'s London Dry Gin','Gordon\'s London Dry Gin','Gordon\'s London Dry Gin','Гордонс Лондон Драй Джин','Klasik London Dry tarzı ardıç aromalı cin','Classic London Dry style juniper-flavoured gin','Klassischer London Dry Gin mit Wacholderaroma','Классический джин London Dry с можжевеловым ароматом'],
    ['Cin','Cin','Beefeater London Dry Gin','Beefeater London Dry Gin','Beefeater London Dry Gin','Бифитер Лондон Драй Джин','Botanik aromalı premium London Dry cin','Premium London Dry gin with botanical aromas','Premium London Dry Gin mit botanischen Aromen','Премиальный джин London Dry с ботаническими ароматами'],
    ['Cin','Cin','Beefeater Pink Gin','Beefeater Pink Gin','Beefeater Pink Gin','Бифитер Пинк Джин','Çilek aromalı pembe cin','Strawberry-flavoured pink gin','Erdbeeraromatisierter Pink Gin','Розовый джин с клубничным вкусом'],
    ['Cin','Cin','Stirling London Dry Gin','Stirling London Dry Gin','Stirling London Dry Gin','Стирлинг Лондон Драй Джин','İskoç kökenli London Dry cin','Scottish-origin London Dry gin','Schottischer London Dry Gin','Шотландский джин London Dry'],
    ['Cin','Cin','Hendrick\'s Gin','Hendrick\'s Gin','Hendrick\'s Gin','Хендрикс Джин','Salatalık ve gül ile aromatize edilmiş İskoç premium cini','Scottish premium gin infused with cucumber and rose','Schottischer Premium-Gin mit Gurke und Rose aromatisiert','Шотландский премиальный джин с огурцом и розой'],
    ['Cin','Cin','Monkey 47','Monkey 47','Monkey 47','Манки 47','Alman Schwarzwald cini, 47 botanik ile üretilmiş','German Schwarzwald gin crafted with 47 botanicals','Deutscher Schwarzwälder Gin aus 47 Botanicals','Немецкий джин Шварцвальд из 47 ботанических ингредиентов'],
    // ── Rom & Cachaça ──
    ['Rom & Cachaça','Rom & Cachaça','Captain Morgan White','Captain Morgan White Rum','Captain Morgan White Rum','Капитан Морган Уайт Ром','Hafif ve tatlı beyaz rom','Light and sweet white rum','Leichter und süßer weißer Rum','Лёгкий и сладкий белый ром'],
    ['Rom & Cachaça','Rom & Cachaça','Captain Morgan Gold','Captain Morgan Gold Rum','Captain Morgan Gold Rum','Капитан Морган Голд Ром','Baharatlı altın rom','Spiced gold rum','Gewürzter Goldener Rum','Пряный золотой ром'],
    ['Rom & Cachaça','Rom & Cachaça','Havana Club','Havana Club','Havana Club','Хавана Клуб','Küba\'nın ikonik rom markası','Cuba\'s iconic rum brand','Kubas ikonische Rummarke','Культовый кубинский ром'],
    ['Rom & Cachaça','Rom & Cachaça','Caribica','Caribica Rum','Caribica Rum','Карибика Ром','Karayip meyve aromalı rom','Caribbean fruit-flavoured rum','Karibischer fruchtiger Rum','Карибский ром с фруктовым ароматом'],
    // ── Tekilalar ──
    ['Tekilalar','Tekilalar','Olmeca Silver','Olmeca Silver Tequila','Olmeca Silver Tequila','Ольмека Сильвер Текила','Meksika silver tekila','Mexican silver tequila','Mexikanische Silver Tequila','Мексиканская серебряная текила'],
    ['Tekilalar','Tekilalar','Pueblo Silver','Pueblo Silver Tequila','Pueblo Silver Tequila','Пуэбло Сильвер Текила','Smooth silver tequila','Smooth silver tequila','Sanfte Silver Tequila','Мягкая серебряная текила'],
    ['Tekilalar','Tekilalar','Patron Silver','Patron Silver Tequila','Patron Silver Tequila','Патрон Сильвер Текила','El Agave\'den elde edilen ultra premium silver tekila','Ultra premium silver tequila from El Agave','Ultra Premium Silver Tequila aus El Agave','Ультрапремиальная серебряная текила из Эль Агаве'],
    ['Tekilalar','Tekilalar','Patron Anejo','Patron Añejo Tequila','Patron Añejo Tequila','Патрон Аньехо Текила','Meşe fıçıda dinlendirilmiş amber renkli premium tekila','Amber-coloured premium tequila aged in oak barrels','Bernsteinfarbige Premium-Tequila, in Eichenfässern gereift','Янтарная премиальная текила, выдержанная в дубовых бочках'],
    // ── Konyak & Brendi ──
    ['Konyak & Brendi','Konyak & Brendi','House Brandy Napoleon','House Brandy Napoleon','House Brandy Napoleon','Хаус Бренди Наполеон','Ev içkisi kaliteli Napoleon brendi','Quality house Napoleon brandy','Qualitatives Hausbrandy Napoleon','Качественный домашний бренди Наполеон'],
    ['Konyak & Brendi','Konyak & Brendi','Legacy Brandy','Legacy Brandy','Legacy Brandy','Легаси Бренди','Premium yerli brendi','Premium local brandy','Premium inländischer Brandy','Премиальный местный бренди'],
    ['Konyak & Brendi','Konyak & Brendi','Rémy Martin VSOP','Rémy Martin V.S.O.P Cognac','Rémy Martin V.S.O.P Cognac','Реми Мартен V.S.O.P Коньяк','Fine Champagne bölgesinden VSOP konjak','VSOP cognac from the Fine Champagne region','VSOP Cognac aus der Fine Champagne Region','Коньяк VSOP из региона Фин Шампань'],
    ['Konyak & Brendi','Konyak & Brendi','Hennessy VS','Hennessy V.S Cognac','Hennessy V.S Cognac','Хеннесси V.S Коньяк','Dünyanın en çok satan konjak markası VS serisi','World\'s best-selling cognac brand VS series','Weltweit meistverkaufte Cognacmarke VS-Serie','Серия VS самого продаваемого в мире бренда коньяка'],
    ['Konyak & Brendi','Konyak & Brendi','Hennessy VSOP','Hennessy V.S.O.P Cognac','Hennessy V.S.O.P Cognac','Хеннесси V.S.O.P Коньяк','Zengin ve karmaşık aromalı Hennessy VSOP konjak','Hennessy V.S.O.P cognac with rich and complex aromas','Hennessy V.S.O.P Cognac mit reichen und komplexen Aromen','Коньяк Хеннесси V.S.O.P с богатым и сложным ароматом'],
    ['Konyak & Brendi','Konyak & Brendi','Martell VS','Martell V.S Cognac','Martell V.S Cognac','Мартель V.S Коньяк','Fransa\'nın köklü konjak markası Martell VS serisi','Martell VS series from France\'s established cognac house','Martell VS-Serie aus Frankreichs traditionsreichem Cognachaus','Серия VS французского коньячного дома Мартель'],
    ['Konyak & Brendi','Konyak & Brendi','Martell VSOP 180','Martell V.S.O.P 180 Cognac','Martell V.S.O.P 180 Cognac','Мартель V.S.O.P 180 Коньяк','Özenle harmanlanmış Martell 180 VSOP konjak','Carefully blended Martell 180 VSOP cognac','Sorgfältig gereifter Martell 180 VSOP Cognac','Тщательно купажированный коньяк Мартель 180 V.S.O.P'],
    // ── Likör ──
    ['Likör','Likör','Irish Cream','Irish Cream Liqueur','Irish Cream Likör','Айриш Крим Ликёр','Viski ve krema bazlı İrlanda liköru','Irish cream and whiskey based liqueur','Irischer Sahne- und Whiskylikör','Ирландский ликёр на основе виски и сливок'],
    ['Likör','Likör','Malibu','Malibu Coconut Liqueur','Malibu Kokoslikör','Малибу Кокосовый Ликёр','Hindistan cevizi aromalı beyaz rom bazlı likör','White rum-based liqueur with coconut taste','Weißrumlikör mit Kokosgeschmack','Белый ром с кокосовым ароматом'],
    ['Likör','Likör','Yerli Likörler','Local Liqueurs','Einheimische Liköre','Местные ликёры','Türkiye\'nin geleneksel yerli likörleri','Turkey\'s traditional local liqueurs','Türkische traditionelle einheimische Liköre','Традиционные турецкие местные ликёры'],
    // ── Vermut ──
    ['Vermut','Vermut','Vermouth Bianco','Vermouth Bianco','Wermut Bianco','Вермут Бьянко','Tatlı beyaz vermut','Sweet white vermouth','Süßer Weißwermut','Сладкий белый вермут'],
    ['Vermut','Vermut','Vermouth Rosso','Vermouth Rosso','Wermut Rosso','Вермут Россо','Tatlı kırmızı İtalyan vermut','Sweet red Italian vermouth','Süßer roter italienischer Wermut','Сладкий красный итальянский вермут'],
    ['Vermut','Vermut','Vermouth Dry','Vermouth Dry','Wermut Dry','Вермут Драй','Kuru beyaz açık içimli vermut','Dry white light-palate vermouth','Trockener weißer Wermut','Сухой белый вермут'],
    // ── Aperatif ──
    ['Aperatif','Aperatif','Campari','Campari','Campari','Кампари','Acı portakal ve bitki aromalı İtalyan likörü','Italian bitter orange and herbal liqueur','Italienischer Bitter mit Orangenschalen und Kräutern','Итальянский биттер с горьким апельсином и травами'],
    ['Aperatif','Aperatif','Aperol','Aperol','Aperol','Аперол','Spritz kokteyline mükemmel eşlik eden hafif portakal likörü','Light orange liqueur perfect for Spritz cocktails','Leichter Orangenlikör, perfekt für Spritz-Cocktails','Лёгкий апельсиновый ликёр, идеальный для коктейля Spritz'],
    // ── Digestiv ──
    ['Digestiv','Digestiv','Jägermeister','Jägermeister','Jägermeister','Егермайстер','56 bitkiyle üretilen Alman bitki liköru','German herbal liqueur made with 56 botanicals','Deutschen Kräuterlikör aus 56 Botanicals','Немецкий травяной ликёр из 56 ботанических ингредиентов'],
    // ── Klasik Kokteyller ──
    ['Klasik Kokteyller','Klasikler','Bloody Mary','Bloody Mary','Bloody Mary','Блади Мэри','Votka, domates suyu, Worcestershire ve tabasco ile klasik kokteyl','Classic cocktail with vodka, tomato juice, Worcestershire and tabasco','Klassischer Cocktail mit Vodka, Tomatensaft, Worcestershire und Tabasco','Классический коктейль с водкой, томатным соком, Вустером и табаско'],
    ['Klasik Kokteyller','Klasikler','Negroni','Negroni','Negroni','Негрони','Cin, kırmızı vermut ve Campari ile klasik İtalyan kokteyil','Classic Italian cocktail with gin, red vermouth and Campari','Klassischer italienischer Cocktail mit Gin, rotem Wermut und Campari','Классический итальянский коктейль с джином, красным вермутом и Кампари'],
    ['Klasik Kokteyller','Klasikler','Espresso Martini','Espresso Martini','Espresso Martini','Эспрессо Мартини','Votka, espresso ve kahve liköru ile hazırlanan enerji veren kokteyl','Energising cocktail with vodka, espresso and coffee liqueur','Belebender Cocktail mit Vodka, Espresso und Kaffeelikör','Бодрящий коктейль с водкой, эспрессо и кофейным ликёром'],
    ['Klasik Kokteyller','Klasikler','Lynchburg Lemonade','Lynchburg Lemonade','Lynchburg Lemonade','Линчберг Лимонад','Jack Daniel\'s, Triple Sec ve limon suyu ile serinletici kokteyl','Refreshing cocktail with Jack Daniel\'s, Triple Sec and lemon juice','Erfrischender Cocktail mit Jack Daniel\'s, Triple Sec und Zitronensaft','Освежающий коктейль с Jack Daniel\'s, Triple Sec и лимонным соком'],
    // ── Yeni Nesil Kokteyller ──
    ['Yeni Nesil Kokteyller','Yeni Nesil','Porn Star Martini','Porn Star Martini','Porn Star Martini','Порн Стар Мартини','Vanilya votka, maracuja ve limon suyundan oluşan popüler kokteyl','Popular cocktail of vanilla vodka, passion fruit and lemon juice','Beliebter Cocktail aus Vanillevodka, Maracuja und Zitronensaft','Популярный коктейль из ванильной водки, маракуйи и лимонного сока'],
    ['Yeni Nesil Kokteyller','Yeni Nesil','East Asia','East Asia','East Asia','Ист Эйша','Japon viskisi, zencefil ve bergamot ile Uzakdogu\'dan ilham alan kokteyl','Far Eastern inspired cocktail with Japanese whisky, ginger and bergamot','Fernost-inspirierter Cocktail mit japanischem Whisky, Ingwer und Bergamotte','Коктейль вдохновлённый Дальним Востоком с японским виски, имбирём и бергамотом'],
    ['Yeni Nesil Kokteyller','Yeni Nesil','Tangerine Sour','Tangerine Sour','Tangerine Sour','Тэнджерин Сауэр','Taze mandalina suyu, votka ve limon köpüğü ile ferahlatıcı ekşili kokteyl','Refreshing sour cocktail with fresh tangerine juice, vodka and lemon foam','Erfrischender Sour mit frischem Mandarinensirup, Vodka und Zitronenschaum','Освежающий кислый коктейль со свежим мандариновым соком, водкой и лимонной пеной'],
    ['Yeni Nesil Kokteyller','Yeni Nesil','Fiesta','Fiesta','Fiesta','Фиеста','Tekila, limon, mango püresi ve acı biberin muhteşem uyumu','Wonderful mix of tequila, lemon, mango puree and chili','Wunderbare Mischung aus Tequila, Zitrone, Mangopüree und Chili','Восхитительное сочетание текилы, лимона, манго-пюре и чили'],
    ['Yeni Nesil Kokteyller','Yeni Nesil','Crimson Apple','Crimson Apple','Crimson Apple','Кримсон Эппл','Kırmızı elma votka, tarçın ve limon suyundan sonbahar kokteyili','Autumn cocktail of red apple vodka, cinnamon and lemon juice','Herbstlicher Cocktail aus Roter-Apfel-Vodka, Zimt und Zitronensaft','Осенний коктейль из водки с красным яблоком, корицей и лимонным соком'],
    ['Yeni Nesil Kokteyller','Yeni Nesil','Foresta Verde','Foresta Verde','Foresta Verde','Форэста Верде','Cin, fesleğen, salatalık ve limon kordonu ile orman temalı imza kokteyil','Forest-themed signature cocktail with gin, basil, cucumber and lime cordial','Waldthematischer Signature-Cocktail mit Gin, Basilikum, Gurke und Limettenkordialen','Фирменный коктейль с лесной тематикой — джин, базилик, огурец и лаймовый кордиал'],
    // ── Alkolsüz Kokteyller ──
    ['Alkolsüz Kokteyller','Alkolsüz Kokteyller','Frappé','Frappé','Frappé','Фраппе','Buzlu kahve ve kremalı serinletici içecek','Iced coffee blended with cream','Eiskalter Kaffee mit cremigem Shake','Ледяной кофе с кремом'],
    ['Alkolsüz Kokteyller','Alkolsüz Kokteyller','Milk Shake','Milk Shake','Milkshake','Молочный коктейль','Kremli soğuk süt ve dondurma ile shake','Cold creamy ice cream milk shake','Kalter cremiger Eisbecher-Milkshake','Холодный молочный коктейль со сливочным мороженым'],
    ['Alkolsüz Kokteyller','Alkolsüz Kokteyller','Rainbow','Rainbow','Rainbow','Рэйнбоу','Renkli meyve suları ve soda ile canlı alkolsüz kokteyl','Vibrant non-alcoholic cocktail with colourful fruit juices and soda','Lebhafter alkoholfreier Cocktail mit bunten Fruchtsäften und Soda','Яркий безалкогольный коктейль с разноцветными фруктовыми соками и содой'],
    ['Alkolsüz Kokteyller','Alkolsüz Kokteyller','Cool Apple','Cool Apple','Cool Apple','Кул Эппл','Taze elma suyu, nane ve zencefil ile ferahlatıcı içecek','Refreshing drink with fresh apple juice, mint and ginger','Erfrischendes Getränk mit frischem Apfelsaft, Minze und Ingwer','Освежающий напиток со свежим яблочным соком, мятой и имбирём'],
    ['Alkolsüz Kokteyller','Alkolsüz Kokteyller','Bubble Lemon','Bubble Lemon','Bubble Lemon','Бабл Лемон','Limon, soda ve buz ile köpüklü alkolsüz limon kokteyili','Bubbly non-alcoholic lemon cocktail with lemon, soda and ice','Sprudelnder alkoholfreier Zitronencocktail mit Zitrone, Soda und Eis','Игристый безалкогольный лимонный коктейль с лимоном, содой и льдом'],
    // ── Kırmızı Şaraplar ──
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Kavaklıdere Selection Öküzgözü & Boğazkere','Kavaklıdere Selection Öküzgözü & Boğazkere','Kavaklıdere Selection Öküzgözü & Boğazkere','Кавакlidere Selection Екюзгезю & Богазкере','Türkiye / Kavaklıdere Selection — Öküzgözü & Boğazkere yerli üzüm çeşitlerinden harmanlanmış kırmızı şarap','Turkey / Kavaklıdere Selection — red blend from native Öküzgözü & Boğazkere grapes','Türkei / Kavaklıdere Selection — Rotwein-Cuvée aus einheimischen Öküzgözü- & Boğazkere-Trauben','Турция / Kavaklıdere Selection — красное купажное из сортов Экюзгезю & Богазкере'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Pendore Syrah','Pendore Syrah','Pendore Syrah','Пэндоре Сира','Türkiye / Pendore — Syrah üzümünden yapılan dolgun gövdeli kırmızı şarap','Turkey / Pendore — full-bodied red wine from Syrah grapes','Türkei / Pendore — Vollmundiger Rotwein aus Syrah-Trauben','Турция / Pendore — насыщенное красное вино из Сиры'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Kocabağ Gravite Cabernet & Boğazkere','Kocabağ Gravite Cabernet Sauvignon & Boğazkere','Kocabağ Gravite Cabernet Sauvignon & Boğazkere','Коджабаг Гравите Каберне Совиньон & Богазкере','Türkiye / Kocabağ Gravite — Cab. Sauvignon ve Boğazkere harmanı','Turkey / Kocabağ Gravite — Cab. Sauvignon & Boğazkere blend','Türkei / Kocabağ Gravite — Cab. Sauvignon & Boğazkere Cuvée','Турция / Kocabağ Gravite — blend Каб. Совиньон & Богазкере'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Ament Cabernet Franc','Ament Cabernet Franc','Ament Cabernet Franc','Амэнт Каберне Фран','Türkiye / Ament — Cabernet Franc üzümünden anıtsal kırmızı','Turkey / Ament — monumental red from Cabernet Franc grapes','Türkei / Ament — Monumentaler Rotwein aus Cabernet Franc','Турция / Ament — монументальное красное из Каберне Фран'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Urla Nero D\'Avola & Urlakarası','Urla Nero D\'Avola & Urlakarası','Urla Nero D\'Avola & Urlakarası','Урла Неро д\'Авола & Урлакарасы','Türkiye / Urla — Nero D\'Avola ve Urlakarası yerli harmanı','Turkey / Urla — Nero D\'Avola & native Urlakarası blend','Türkei / Urla — Nero D\'Avola & einheimische Urlakarası Cuvée','Турция / Urla — купаж Неро д\'Авола и Урлакарасы'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','AYDIN Prodom Petit Verdot & Cab. Franc & Syrah','AYDIN Prodom Petit Verdot & Cab. Franc & Syrah','AYDIN Prodom Petit Verdot & Cab. Franc & Syrah','Айдын Продом Пети Вердо & Каб. Фран & Сира','Türkiye / AYDIN Prodom — Petit Verdot, Cabernet Franc ve Syrah harmanı','Turkey / AYDIN Prodom — Petit Verdot, Cabernet Franc & Syrah blend','Türkei / AYDIN Prodom — Petit Verdot, Cabernet Franc & Syrah Cuvée','Турция / AYDIN Prodom — купаж Пети Вердо, Каберне Фран & Сира'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Vinkara Kalecik Karası','Vinkara Kalecik Karası','Vinkara Kalecik Karası','Винкара Каледжик Карасы','Türkiye / Vinkara — Anadolu\'nun yerli Kalecik Karası üzümünden','Turkey / Vinkara — from Anatolia\'s native Kalecik Karası grape','Türkei / Vinkara — aus der einheimischen Kalecik-Karası-Traube Anatoliens','Турция / Vinkara — из родного анатолийского сорта Каледжик Карасы'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Chateau La Croix Lartigue','Chateau La Croix Lartigue — Cabernet Franc & Merlot','Chateau La Croix Lartigue — Cabernet Franc & Merlot','Шато Ла Круа Лартиг — Каберне Фран & Мерло','Fransa / Chateau La Croix Lartigue — Cab. Franc ve Merlot harmanı','France / Chateau La Croix Lartigue — Cab. Franc & Merlot blend','Frankreich / Chateau La Croix Lartigue — Cuvée Cab. Franc & Merlot','Франция / Шато Ла Круа Лартиг — купаж Каберне Фран & Мерло'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Baron de Lestac Rouge','Baron de Lestac Rouge — Merlot & Cab. Franc & Cab. Sauvignon','Baron de Lestac Rouge — Merlot & Cab. Franc & Cab. Sauvignon','Барон де Лестак Руж — Мерло & Каб. Фран & Каб. Совиньон','Fransa / Baron de Lestac Rouge — Merlot, Cab. Franc ve Cab. Sauvignon harmanı','France / Baron de Lestac Rouge — Merlot, Cab. Franc & Cab. Sauvignon','Frankreich / Baron de Lestac Rouge — Merlot, Cab. Franc & Cab. Sauvignon Cuvée','Франция / Барон де Лестак Руж — купаж Мерло, Каб. Фран & Каб. Совиньон'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Montes Purple Angel Carmenere','Montes Purple Angel — Carmenere','Montes Purple Angel — Carmenere','Монтес Пёрпл Энджел — Карменер','Şili / Montes Purple Angel — Şili\'nin ikonik Carmenere üzümünden ihtişamlı kırmızı','Chile / Montes Purple Angel — majestic red from Chile\'s iconic Carmenere grape','Chile / Montes Purple Angel — Majestätischer Rotwein aus Chiles ikonischer Carmenere-Traube','Чили / Монтес Пёрпл Энджел — величественное красное из иконного Карменера'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Kaiken Estate Malbec','Kaiken Estate Malbec','Kaiken Estate Malbec','Кайкен Эстейт Мальбек','Arjantin / Kaiken Estate — Mendoza\'dan zengin aromalı Malbec','Argentina / Kaiken Estate — richly aromatic Malbec from Mendoza','Argentinien / Kaiken Estate — Aromatisch reicher Malbec aus Mendoza','Аргентина / Kaiken Estate — богатый ароматом Мальбек из Мендосы'],
    ['Kırmızı Şaraplar','Kırmızı Şaraplar','Castellani Chianti Classico','Castellani Chianti Classico — Sangiovese & Cab. Sauvignon','Castellani Chianti Classico — Sangiovese & Cab. Sauvignon','Кастеллани Кьянти Классико — Санджовезе & Каб. Совиньон','İtalya / Castellani Chianti Classico — Sangiovese ve Cab. Sauvignon harmanı','Italy / Castellani Chianti Classico — Sangiovese & Cab. Sauvignon blend','Italien / Castellani Chianti Classico — Sangiovese & Cab. Sauvignon Cuvée','Италия / Кастеллани Кьянти Классико — купаж Санджовезе & Каб. Совиньон'],
    // ── Beyaz Şaraplar ──
    ['Beyaz Şaraplar','Beyaz Şaraplar','Cotes d\'Avanos Narince','Cotes d\'Avanos Narince','Cotes d\'Avanos Narince','Котес д\'Аванос Нариндже','Türkiye / Cotes d\'Avanos — Narince yerli beyaz üzümünden ferah ve mineral beyaz şarap','Turkey / Cotes d\'Avanos — fresh mineral white from native Narince grapes','Türkei / Cotes d\'Avanos — Frischer mineralischer Weißwein aus einheimischer Narince-Traube','Турция / Cotes d\'Avanos — свежее минеральное белое из сорта Нариндже'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Cotes d\'Avanos Chardonnay','Cotes d\'Avanos Chardonnay','Cotes d\'Avanos Chardonnay','Котес д\'Аванос Шардоне','Türkiye / Cotes d\'Avanos — Chardonnay üzümünden Kapadokya beyazı','Turkey / Cotes d\'Avanos — Cappadocian white from Chardonnay grapes','Türkei / Cotes d\'Avanos — Kappadokischer Chardonnay','Турция / Cotes d\'Avanos — каппадокийское белое из Шардоне'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Kavaklıdere Selection Narince & Emir','Kavaklıdere Selection Narince & Emir','Kavaklıdere Selection Narince & Emir','Кавакlidere Selection Нариндже & Эмир','Türkiye / Kavaklıdere Selection — Narince ve Emir harmanı','Turkey / Kavaklıdere Selection — Narince & Emir blend','Türkei / Kavaklıdere Selection — Narince & Emir Cuvée','Турция / Kavaklıdere Selection — купаж Нариндже & Эмир'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Sultaniye','Sultaniye Beyaz','Sultaniye Weißwein','Султание Белое','Türkiye / Sultaniye üzümünden hafif ve çiçeksi beyaz şarap','Turkey / Light floral white wine from Sultaniye grapes','Türkei / Leichter und blumiger Weißwein aus Sultaniye-Trauben','Турция / Лёгкое цветочное белое из сорта Султание'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Paşaeli Sıdalan Beyaz','Paşaeli Sıdalan White','Paşaeli Sıdalan Weißwein','Пашаэли Сыдалан Белое','Türkiye / Paşaeli Sıdalan Beyaz — Ege bölgesinin yerli Sıdalan üzümünden özel beyaz','Turkey / Paşaeli Sıdalan White — special white from Aegean Sıdalan variety','Türkei / Paşaeli Sıdalan Weißwein — Besonderer Weißwein aus ägäischer Sıdalan-Rebsorte','Турция / Пашаэли Сыдалан Белое — особое белое из эгейского сорта Сыдалан'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Kocabağ Kapodokya Emir','Kocabağ Cappadocia Emir','Kocabağ Kappadokien Emir','Коджабаг Каппадокия Эмир','Türkiye / Kocabağ Kapadokya — Emir üzümünden mineral ve asidik beyaz','Turkey / Kocabağ Cappadocia — mineral and crisp white from Emir grapes','Türkei / Kocabağ Kappadokien — Mineralischer und knackiger Weißwein aus Emir-Trauben','Турция / Kocabağ Каппадокия — минеральное и хрустящее белое из сорта Эмир'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Pacem Sauvignon Blanc','Pacem Sauvignon Blanc','Pacem Sauvignon Blanc','Пачем Совиньон Блан','Türkiye / Pacem — Sauvignon Blanc üzümünden tropikal aromalı beyaz','Turkey / Pacem — tropical-aromatic white from Sauvignon Blanc','Türkei / Pacem — Tropisch aromatischer Weißwein aus Sauvignon Blanc','Турция / Pacem — тропически-ароматное белое из Совиньон Блан'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Vinolus Narince','Vinolus Narince Beyaz','Vinolus Narince Weißwein','Винолус Нариндже Белое','Türkiye / Vinolus — Narince üzümünden aromatik Anadolu beyazı','Turkey / Vinolus — aromatic Anatolian white from Narince grapes','Türkei / Vinolus — Aromatischer anatolischer Weißwein aus Narince-Trauben','Турция / Vinolus — ароматное анатолийское белое из Нариндже'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Baron de Lestac Blanc','Baron de Lestac Blanc — Sauvignon Blanc & Sémillon','Baron de Lestac Blanc — Sauvignon Blanc & Sémillon','Барон де Лестак Блан — Совиньон Блан & Семийон','Fransa / Baron de Lestac Blanc — Sauvignon Blanc ve Sémillon harmanı','France / Baron de Lestac Blanc — Sauvignon Blanc & Sémillon blend','Frankreich / Baron de Lestac Blanc — Sauvignon Blanc & Sémillon Cuvée','Франция / Барон де Лестак Блан — купаж Совиньон Блан & Семийон'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Chablis Le Classique','Chablis Le Classique — Chardonnay','Chablis Le Classique — Chardonnay','Шабли Ле Классик — Шардоне','Fransa / Chablis Le Classique — Burgundy bölgesinden kuru ve mineral Chardonnay','France / Chablis Le Classique — dry mineral Chardonnay from Burgundy','Frankreich / Chablis Le Classique — Trockener mineralischer Chardonnay aus Burgund','Франция / Шабли Ле Классик — сухое минеральное Шардоне из Бургундии'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Santa Helena Sauvignon Blanc','Santa Helena Varietal Sauvignon Blanc','Santa Helena Varietal Sauvignon Blanc','Санта Элена Совиньон Блан','Şili / Santa Helena — ferah ve aromatik Sauvignon Blanc','Chile / Santa Helena — fresh and aromatic Sauvignon Blanc','Chile / Santa Helena — Frischer und aromatischer Sauvignon Blanc','Чили / Санта Элена — свежий и ароматный Совиньон Блан'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Chameleon Chenin Blanc','Chameleon Chenin Blanc','Chameleon Chenin Blanc','Хамелеон Шенен Блан','Güney Afrika / Chameleon — meyvemsi ve canlı Chenin Blanc','South Africa / Chameleon — fruity and vibrant Chenin Blanc','Südafrika / Chameleon — Fruchtiger und lebhafter Chenin Blanc','ЮАР / Chameleon — фруктовый и живой Шенен Блан'],
    ['Beyaz Şaraplar','Beyaz Şaraplar','Prinz von Hessen Riesling','Prinz von Hessen Riesling','Prinz von Hessen Riesling','Принц фон Хессен Рислинг','Almanya / Prinz v. Hessen — Rheingau bölgesinden zarif ve dengeli Riesling','Germany / Prinz v. Hessen — elegant and balanced Riesling from Rheingau','Deutschland / Prinz v. Hessen — Eleganter und ausgewogener Riesling aus Rheingau','Германия / Prinz v. Hessen — элегантный и сбалансированный Рислинг из Рейнгау'],
    // ── Pembe Şaraplar ──
    ['Pembe Şaraplar','Pembe Şaraplar','Suvla Rose Karasakız','Suvla Rose — Karasakız','Suvla Rosé — Karasakız','Сувла Розе — Карасакыз','Türkiye / Suvla Rose — Karasakız üzümünden zarif Ege rozesi','Turkey / Suvla Rose — elegant Aegean rosé from Karasakız grapes','Türkei / Suvla Rosé — Elegantes ägäisches Rosé aus Karasakız-Trauben','Турция / Suvla Розе — элегантный эгейский розе из сорта Карасакыз'],
    ['Pembe Şaraplar','Pembe Şaraplar','Kavaklıdere Rosato Çalkarası','Kavaklıdere Rosato — Çalkarası','Kavaklıdere Rosato — Çalkarası','Кавакlidere Розато — Чалкарасы','Türkiye / Kavaklıdere Rosato — Çalkarası yerli üzümünden taze pembe şarap','Turkey / Kavaklıdere Rosato — fresh rosé from native Çalkarası grapes','Türkei / Kavaklıdere Rosato — Frisches Rosé aus einheimischen Çalkarası-Trauben','Турция / Кавакlidere Розато — свежий розе из сорта Чалкарасы'],
    ['Pembe Şaraplar','Pembe Şaraplar','Felici Cabernet Sauvignon Rosé','Felici Rosé — Cabernet Sauvignon','Felici Rosé — Cabernet Sauvignon','Фэличи Розе — Каберне Совиньон','Türkiye / Felici — Cabernet Sauvignon üzümünden meyvemsi pembe','Turkey / Felici — fruity rosé from Cabernet Sauvignon grapes','Türkei / Felici — Fruchtiges Rosé aus Cabernet Sauvignon','Турция / Фэличи — фруктовый розе из Каберне Совиньон'],
    ['Pembe Şaraplar','Pembe Şaraplar','Urla Serendias Pinot Noir & Kalecik Karası','Urla Serendias — Pinot Noir & Kalecik Karası','Urla Serendias — Pinot Noir & Kalecik Karası','Урла Серендиас — Пино Нуар & Каледжик Карасы','Türkiye / Urla Serendias — Pinot Noir ve Kalecik Karası harmanı pembe şarap','Turkey / Urla Serendias — Pinot Noir & Kalecik Karası rosé blend','Türkei / Urla Serendias — Rosé-Cuvée aus Pinot Noir & Kalecik Karası','Турция / Урла Серендиас — розе из купажа Пино Нуар & Каледжик Карасы'],
    ['Pembe Şaraplar','Pembe Şaraplar','Villa Moncigale Cicada l\'Eveil','Villa Moncigale Cicada l\'Eveil — Cinsault & Grenache & Syrah & Rolle','Villa Moncigale Cicada l\'Eveil — Cinsault & Grenache & Syrah & Rolle','Вилла Монсигаль Цикада л\'Эвей','Fransa / Villa Moncigale — Cinsault, Grenache, Syrah ve Rolle harmanı Provençal rozesi','France / Villa Moncigale — Provençal rosé blend of Cinsault, Grenache, Syrah & Rolle','Frankreich / Villa Moncigale — Provenzalisches Rosé-Cuvée aus Cinsault, Grenache, Syrah & Rolle','Франция / Вилла Монсигаль — провансальский розе из Сенсо, Гренаша, Сиры & Ролля'],
    ['Pembe Şaraplar','Pembe Şaraplar','Moncigale Mediteo Rosé','Moncigale Mediteo Méditerranée — Grenache & Syrah & Cinsault','Moncigale Mediteo Méditerranée — Grenache & Syrah & Cinsault','Монсигаль Медитео Розе','Fransa / Moncigale Mediteo — Grenache, Syrah ve Cinsault harmanı Akdeniz rozesi','France / Moncigale Mediteo — Mediterranean rosé blend of Grenache, Syrah & Cinsault','Frankreich / Moncigale Mediteo — Mediterranes Rosé-Cuvée aus Grenache, Syrah & Cinsault','Франция / Монсигаль Медитео — средиземноморский розе из Гренаша, Сиры & Сенсо'],
    // ── Şampanyalar ──
    ['Şampanyalar','Şampanyalar','G.H. Mumm Gordon Rouge','G.H. Mumm Gordon Rouge — Chardonnay & Pinot Meunier & Pinot Noir','G.H. Mumm Gordon Rouge — Chardonnay & Pinot Meunier & Pinot Noir','Г.Х. Мум Гордон Руж Шампань','Fransa / G.H. Mumm — Reims\'in ikonik prestige cuvée şampanyası','France / G.H. Mumm — Reims iconic prestige cuvée Champagne','Frankreich / G.H. Mumm — Rheims\' ikonisches Prestige-Cuvée-Champagner','Франция / G.H. Mumm — знаменитое Prestige Cuvée из Реймса'],
    ['Şampanyalar','Şampanyalar','Moët & Chandon Brut Imperial','Moët & Chandon Brut Impérial — Chardonnay & Pinot Meunier & Pinot Noir','Moët & Chandon Brut Impérial — Chardonnay & Pinot Meunier & Pinot Noir','Моэт & Шандон Брют Империал Шампань','Fransa / Moët & Chandon — tüm dünyanın sevdiği ikonik Brut şampanya','France / Moët & Chandon — the world\'s beloved iconic Brut Champagne','Frankreich / Moët & Chandon — Der weltbekannte ikonische Brut-Champagner','Франция / Моэт & Шандон — всемирно любимое культовое Брют Шампанское'],
    ['Şampanyalar','Şampanyalar','Dom Pérignon','Dom Pérignon — Chardonnay & Pinot Noir','Dom Pérignon — Chardonnay & Pinot Noir','Дом Периньон Шампань','Fransa / Dom Pérignon — dünyanın en prestijli vintage şampanya markası','France / Dom Pérignon — world\'s most prestigious vintage Champagne','Frankreich / Dom Pérignon — Weltprestigiösste Vintage-Champagner-Marke','Франция / Дом Периньон — самый престижный винтажный Шампанский бренд мира'],
    ['Şampanyalar','Şampanyalar','Ruffino Prosecco','Ruffino Prosecco — Glera','Ruffino Prosecco — Glera','Руффино Просекко — Глера','İtalya / Ruffino Prosecco — Veneto bölgesinden taze ve canlı köpüklü şarap','Italy / Ruffino Prosecco — fresh and lively sparkling wine from Veneto','Italien / Ruffino Prosecco — Frischer und lebhafter Schaumwein aus Venetien','Италия / Руффино Просекко — свежее и живое игристое вино из Венето'],
];

// ─────────────────────────────────────────────────────────────────
// ADIM 3: Ürünleri food library'e ekle, QR menü 18'e bağla
// ─────────────────────────────────────────────────────────────────
$addedLib  = 0;
$addedQr   = 0;
$skippedLib = 0;
$skippedQr  = 0;

foreach ($products as $p) {
    // $p: [libCatTr, qrCatTr, titleTr, titleEn, titleDe, titleRu, descTr, descEn, descDe, descRu]
    [$libCatTr, $qrCatTr, $titleTr, $titleEn, $titleDe, $titleRu, $descTr, $descEn, $descDe, $descRu] = $p;

    $libCatId = $libCatMap[$libCatTr] ?? null;
    $qrCatId  = $qrCatMap[$qrCatTr] ?? null;

    if (!$libCatId) {
        echo "UYARI: Food library kategori bulunamadı: {$libCatTr}\n";
        continue;
    }
    if (!$qrCatId) {
        echo "UYARI: QR menü kategori bulunamadı: {$qrCatTr}\n";
        continue;
    }

    // Food library ürün kontrolü
    $product = FoodProduct::where('branch_id', $branchId)
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title,'$.tr'))=?", [$titleTr])
        ->first();

    if (!$product) {
        $product = FoodProduct::create([
            'branch_id'        => $branchId,
            'food_category_id' => $libCatId,
            'image'            => $sharedImage,
            'title'            => ['tr'=>$titleTr,'en'=>$titleEn,'de'=>$titleDe,'ru'=>$titleRu],
            'description'      => ['tr'=>$descTr,'en'=>$descEn,'de'=>$descDe,'ru'=>$descRu],
            'ingredients'      => null,
            'price'            => 0,
            'allergens'        => null,
            'is_active'        => true,
            'sort_order'       => 0,
        ]);
        $addedLib++;
        echo "LIB ÜRÜN EKLENDİ: [{$libCatTr}] {$titleTr}\n";
    } else {
        $skippedLib++;
    }

    // QR menü item kontrolü
    $qrExists = QrMenuItem::where('category_id', $qrCatId)
        ->where('food_product_id', $product->id)
        ->exists();

    if (!$qrExists) {
        $maxSort = QrMenuItem::where('category_id', $qrCatId)->max('sort_order') ?? 0;
        QrMenuItem::create([
            'category_id'     => $qrCatId,
            'food_product_id' => $product->id,
            'title'           => $product->title,
            'description'     => $product->description,
            'price'           => 0,
            'is_active'       => true,
            'sort_order'      => $maxSort + 1,
        ]);
        $addedQr++;
    } else {
        $skippedQr++;
    }
}

echo "\n";
echo "✓ Food Library: {$addedLib} ürün eklendi, {$skippedLib} atlandı.\n";
echo "✓ QR Menü 18: {$addedQr} ürün bağlandı, {$skippedQr} zaten mevcuttu.\n";
