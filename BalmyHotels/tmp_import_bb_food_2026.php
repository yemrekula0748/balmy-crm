<?php

declare(strict_types=1);

$basePath = rtrim((string) (getenv('BALMY_APP_BASE') ?: __DIR__), '/\\');
require $basePath . '/vendor/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodCategory;
use App\Models\FoodProduct;
use Illuminate\Support\Facades\DB;

const BRANCH_ID = 1;

function product(
    string $category,
    array $title,
    array $ingredients,
    array $allergens,
    float $protein,
    float $carbs,
    float $fat
): array {
    $description = [
        'tr' => "{$title['tr']}; {$ingredients['tr']} ile özenle hazırlanır.",
        'en' => "{$title['en']}; carefully prepared with {$ingredients['en']}.",
        'de' => "{$title['de']}; sorgfältig zubereitet mit {$ingredients['de']}.",
        'ru' => "{$title['ru']}; тщательно приготовлено из следующих ингредиентов: {$ingredients['ru']}.",
    ];

    return compact('category', 'title', 'description', 'ingredients', 'allergens', 'protein', 'carbs', 'fat')
        + ['calories' => round(($protein * 4) + ($carbs * 4) + ($fat * 9))];
}

$categories = [
    'Başlangıçlar' => ['icon' => '🥪', 'sort' => 1, 'title' => ['tr'=>'Başlangıçlar','en'=>'Starters','de'=>'Vorspeisen','ru'=>'Закуски']],
    'Ara Sıcaklar' => ['icon' => '🍝', 'sort' => 2, 'title' => ['tr'=>'Ara Sıcaklar','en'=>'Hot Appetizers','de'=>'Warme Vorspeisen','ru'=>'Горячие закуски']],
    'Salatalar' => ['icon' => '🥗', 'sort' => 3, 'title' => ['tr'=>'Salatalar','en'=>'Salads','de'=>'Salate','ru'=>'Салаты']],
    'Ana Yemekler' => ['icon' => '🍽️', 'sort' => 4, 'title' => ['tr'=>'Ana Yemekler','en'=>'Main Courses','de'=>'Hauptgerichte','ru'=>'Основные блюда']],
    'Tatlılar' => ['icon' => '🍮', 'sort' => 5, 'title' => ['tr'=>'Tatlılar','en'=>'Desserts','de'=>'Desserts','ru'=>'Десерты']],
    'Sandviç & Burgerler' => ['icon' => '🍔', 'sort' => 6, 'title' => ['tr'=>'Sandviç & Burgerler','en'=>'Sandwiches & Burgers','de'=>'Sandwiches & Burger','ru'=>'Сэндвичи и бургеры']],
    'Makarnalar' => ['icon' => '🍝', 'sort' => 7, 'title' => ['tr'=>'Makarnalar','en'=>'Pasta','de'=>'Pasta','ru'=>'Паста']],
    'Pide & Pizza' => ['icon' => '🍕', 'sort' => 8, 'title' => ['tr'=>'Pide & Pizza','en'=>'Pide & Pizza','de'=>'Pide & Pizza','ru'=>'Пиде и пицца']],
    '7 Bölge 7 Lezzet' => ['icon' => '🇹🇷', 'sort' => 9, 'title' => ['tr'=>'7 Bölge 7 Lezzet','en'=>'Seven Regions, Seven Flavors','de'=>'Sieben Regionen, sieben Geschmäcker','ru'=>'Семь регионов — семь вкусов']],
];

$products = [
    product('Başlangıçlar', ['tr'=>'Narenciye & Tropik Çupra','en'=>'Citrus & Tropical Sea Bream','de'=>'Dorade mit Zitrus & Tropik','ru'=>'Дорада с цитрусами и тропическими нотами'], ['tr'=>'çupra, narenciye, tropik meyveler, deniz börülcesi turşusu','en'=>'sea bream, citrus, tropical fruit, pickled samphire','de'=>'Dorade, Zitrusfrüchte, tropische Früchte, eingelegter Meeresspargel','ru'=>'дорада, цитрусовые, тропические фрукты, маринованный морской аспарагус'], ['balik'], 34, 18, 16),
    product('Başlangıçlar', ['tr'=>'Füme Pancar Carpaccio','en'=>'Smoked Beetroot Carpaccio','de'=>'Carpaccio von geräucherter Roter Bete','ru'=>'Карпаччо из копчёной свёклы'], ['tr'=>'füme pancar, turunç vinaigrette, kapari, dereotu yağı, ekşi mayalı kruton','en'=>'smoked beetroot, bitter-orange vinaigrette, capers, dill oil, sourdough croutons','de'=>'geräucherte Rote Bete, Bitterorangen-Vinaigrette, Kapern, Dillöl, Sauerteig-Croûtons','ru'=>'копчёная свёкла, винегрет из горького апельсина, каперсы, укропное масло, гренки на закваске'], ['gluten'], 6, 34, 14),
    product('Ara Sıcaklar', ['tr'=>'Kemer Piliç','en'=>'Kemer Chicken','de'=>'Kemer-Hähnchen','ru'=>'Цыплёнок по-кемерски'], ['tr'=>'piliç, isli mısır kreması, kültür mantarı, köy kuskusu','en'=>'chicken, smoked corn cream, cultivated mushrooms, rustic couscous','de'=>'Hähnchen, geräucherte Maiscreme, Champignons, rustikaler Couscous','ru'=>'цыплёнок, крем из копчёной кукурузы, шампиньоны, деревенский кускус'], ['gluten','sut'], 38, 42, 18),
    product('Ara Sıcaklar', ['tr'=>'Rigatoni Funghi & Zafferano','en'=>'Rigatoni Funghi & Zafferano','de'=>'Rigatoni Funghi & Zafferano','ru'=>'Ригатони с грибами и шафраном'], ['tr'=>'rigatoni, sotelenmiş mantar, safran, parmesan, zeytinyağı','en'=>'rigatoni, sautéed mushrooms, saffron, parmesan, olive oil','de'=>'Rigatoni, sautierte Pilze, Safran, Parmesan, Olivenöl','ru'=>'ригатони, обжаренные грибы, шафран, пармезан, оливковое масло'], ['gluten','sut'], 20, 78, 24),
    product('Ara Sıcaklar', ['tr'=>'Çıtır Nohutlu Kereviz Röşti','en'=>'Celery Rösti with Crispy Chickpeas','de'=>'Sellerie-Rösti mit knusprigen Kichererbsen','ru'=>'Рёшти из сельдерея с хрустящим нутом'], ['tr'=>'kereviz, patates, nohut, yoğurt kreması, ot yağı, baharatlar','en'=>'celery, potato, chickpeas, yogurt cream, herb oil, spices','de'=>'Sellerie, Kartoffeln, Kichererbsen, Joghurtcreme, Kräuteröl, Gewürze','ru'=>'сельдерей, картофель, нут, йогуртовый крем, масло с травами, специи'], ['kereviz','sut'], 14, 54, 20),
    product('Salatalar', ['tr'=>'Rezene & Narenciye','en'=>'Fennel & Citrus','de'=>'Fenchel & Zitrus','ru'=>'Фенхель и цитрусы'], ['tr'=>'rezene, portakal, limon kabuğu, zeytinyağı, mevsim yeşillikleri','en'=>'fennel, orange, lemon zest, olive oil, seasonal greens','de'=>'Fenchel, Orange, Zitronenschale, Olivenöl, saisonale Blattsalate','ru'=>'фенхель, апельсин, лимонная цедра, оливковое масло, сезонная зелень'], [], 4, 24, 12),
    product('Salatalar', ['tr'=>'Izgara Yeşil Sebzeler','en'=>'Grilled Green Vegetables','de'=>'Gegrilltes grünes Gemüse','ru'=>'Зелёные овощи на гриле'], ['tr'=>'bamya, kabak, yeşil sebzeler, tahin, limon, zeytinyağı','en'=>'okra, zucchini, green vegetables, tahini, lemon, olive oil','de'=>'Okra, Zucchini, grünes Gemüse, Tahini, Zitrone, Olivenöl','ru'=>'бамия, цукини, зелёные овощи, тахини, лимон, оливковое масло'], ['susam'], 8, 28, 18),
    product('Salatalar', ['tr'=>'Köz Pancar & Vişne','en'=>'Roasted Beetroot & Sour Cherry','de'=>'Geröstete Rote Bete & Sauerkirsche','ru'=>'Печёная свёкла и вишня'], ['tr'=>'köz pancar, vişne, lor peyniri kreması, yeşillikler','en'=>'roasted beetroot, sour cherries, curd-cheese cream, greens','de'=>'geröstete Rote Bete, Sauerkirschen, Lor-Frischkäsecreme, Blattsalate','ru'=>'печёная свёкла, вишня, крем из творожного сыра, зелень'], ['sut'], 10, 34, 14),
    product('Ana Yemekler', ['tr'=>'Levrek Izgara','en'=>'Grilled Sea Bass','de'=>'Gegrillter Wolfsbarsch','ru'=>'Сибас на гриле'], ['tr'=>'levrek fileto, kuzukulağı, polenta, zeytinyağı, limon','en'=>'sea bass fillet, sorrel, polenta, olive oil, lemon','de'=>'Wolfsbarschfilet, Sauerampfer, Polenta, Olivenöl, Zitrone','ru'=>'филе сибаса, щавель, полента, оливковое масло, лимон'], ['balik'], 42, 36, 22),
    product('Ana Yemekler', ['tr'=>'Piliç Şaşlık','en'=>'Chicken Shashlik','de'=>'Hähnchen-Schaschlik','ru'=>'Шашлык из цыплёнка'], ['tr'=>'piliç, ızgara pırasa, köz patlıcan, biber jeli, baharatlar','en'=>'chicken, grilled leek, roasted eggplant, pepper jelly, spices','de'=>'Hähnchen, gegrillter Lauch, geröstete Aubergine, Paprikagelee, Gewürze','ru'=>'цыплёнок, лук-порей на гриле, печёный баклажан, желе из перца, специи'], [], 46, 30, 20),
    product('Ana Yemekler', ['tr'=>'Izgara Ahtapot','en'=>'Grilled Octopus','de'=>'Gegrillter Oktopus','ru'=>'Осьминог на гриле'], ['tr'=>'ahtapot, safranlı arpa şehriye, köz kırmızı biber püresi, narenciye yağı','en'=>'octopus, saffron orzo, roasted red-pepper purée, citrus oil','de'=>'Oktopus, Safran-Orzo, Püree aus gerösteter roter Paprika, Zitrusöl','ru'=>'осьминог, орзо с шафраном, пюре из печёного красного перца, цитрусовое масло'], ['yumusakcalar','gluten'], 40, 52, 20),
    product('Ana Yemekler', ['tr'=>'Tavşan Rosto','en'=>'Roast Rabbit','de'=>'Kaninchenbraten','ru'=>'Жаркое из кролика'], ['tr'=>'tavşan eti, köz patates-havuç kreması, kinoa, nar-sumak emülsiyonu','en'=>'rabbit, roasted potato-carrot cream, quinoa, pomegranate-sumac emulsion','de'=>'Kaninchen, Creme aus gerösteten Kartoffeln und Karotten, Quinoa, Granatapfel-Sumach-Emulsion','ru'=>'кролик, крем из печёного картофеля и моркови, киноа, гранатово-сумаховая эмульсия'], ['sut'], 45, 50, 24),
    product('Ana Yemekler', ['tr'=>'Dana Antrikot','en'=>'Beef Ribeye','de'=>'Rinderentrecôte','ru'=>'Говяжий антрекот'], ['tr'=>'dana antrikot, patates püresi, brokoli, hardallı demi-glace','en'=>'beef ribeye, mashed potato, broccoli, mustard demi-glace','de'=>'Rinderentrecôte, Kartoffelpüree, Brokkoli, Senf-Demi-glace','ru'=>'говяжий антрекот, картофельное пюре, брокколи, демиглас с горчицей'], ['sut','hardal','kereviz'], 52, 44, 38),
    product('Tatlılar', ['tr'=>'Süt & Çam Balı','en'=>'Milk & Pine Honey','de'=>'Milch & Kiefernhonig','ru'=>'Молоко и сосновый мёд'], ['tr'=>'süt, çam balı, is aromalı muhallebi, Lotus bisküvi','en'=>'milk, pine honey, smoked milk pudding, Lotus biscuits','de'=>'Milch, Kiefernhonig, rauchig aromatisierter Pudding, Lotus-Kekse','ru'=>'молоко, сосновый мёд, пудинг с дымным ароматом, печенье Lotus'], ['sut','gluten','soya'], 10, 62, 18),
    product('Tatlılar', ['tr'=>'Kabak Tatlısı','en'=>'Turkish Pumpkin Dessert','de'=>'Türkisches Kürbisdessert','ru'=>'Тыквенный десерт'], ['tr'=>'bal kabağı, şeker, limon kabuğu, tahin, ceviz','en'=>'pumpkin, sugar, lemon zest, tahini, walnuts','de'=>'Kürbis, Zucker, Zitronenschale, Tahini, Walnüsse','ru'=>'тыква, сахар, лимонная цедра, тахини, грецкие орехи'], ['susam','kuruyemis'], 7, 68, 20),
    product('Tatlılar', ['tr'=>'Kahve & Kakule','en'=>'Coffee & Cardamom','de'=>'Kaffee & Kardamom','ru'=>'Кофе и кардамон'], ['tr'=>'cold brew kreması, kakuleli bisküvi, süt reçeli','en'=>'cold-brew cream, cardamom biscuit, milk caramel','de'=>'Cold-Brew-Creme, Kardamomkeks, Milchkaramell','ru'=>'крем с холодным кофе, печенье с кардамоном, молочная карамель'], ['sut','gluten'], 9, 58, 22),
    product('Başlangıçlar', ['tr'=>'Toscana Tavuk Ciğeri Bruschetta','en'=>'Tuscan Chicken Liver Bruschetta','de'=>'Toskanische Bruschetta mit Hühnerleber','ru'=>'Тосканская брускетта с куриной печенью'], ['tr'=>'köy ekmeği, tavuk ciğeri kreması, vişne reçeli, zeytin sosu, roka','en'=>'rustic bread, chicken-liver cream, sour-cherry jam, olive sauce, arugula','de'=>'Landbrot, Hühnerlebercreme, Sauerkirschkonfitüre, Olivensauce, Rucola','ru'=>'деревенский хлеб, крем из куриной печени, вишнёвый джем, оливковый соус, руккола'], ['gluten','sut'], 20, 42, 18),
    product('Başlangıçlar', ['tr'=>'Narenciye Gravlax Somon','en'=>'Citrus Gravlax Salmon','de'=>'Zitrus-Gravlax vom Lachs','ru'=>'Лосось гравлакс с цитрусами'], ['tr'=>'gravlax somon, narenciye, deniz börülcesi turşusu, sumaklı patates, kuru domates tapenade','en'=>'gravlax salmon, citrus, pickled samphire, sumac potatoes, sun-dried tomato tapenade','de'=>'Gravlax-Lachs, Zitrusfrüchte, eingelegter Meeresspargel, Sumach-Kartoffeln, Tapenade aus getrockneten Tomaten','ru'=>'лосось гравлакс, цитрусовые, маринованный морской аспарагус, картофель с сумахом, тапенада из вяленых томатов'], ['balik'], 30, 34, 20),
    product('Sandviç & Burgerler', ['tr'=>'Şarküteri Wrap','en'=>'Deli Wrap','de'=>'Feinkost-Wrap','ru'=>'Деликатесный ролл'], ['tr'=>'buğday tortilla, hindi füme, avokado püresi, cheddar peyniri, yeşillikler','en'=>'wheat tortilla, smoked turkey, avocado purée, cheddar, greens','de'=>'Weizentortilla, geräucherte Pute, Avocadopüree, Cheddar, Blattsalat','ru'=>'пшеничная тортилья, копчёная индейка, пюре из авокадо, чеддер, зелень'], ['gluten','sut'], 32, 48, 24),
    product('Sandviç & Burgerler', ['tr'=>'Swiss Mushroom Burger','en'=>'Swiss Mushroom Burger','de'=>'Swiss Mushroom Burger','ru'=>'Бургер с грибами и швейцарским сыром'], ['tr'=>'dana köfte, burger ekmeği, sotelenmiş mantar, İsviçre peyniri, patates tava','en'=>'beef patty, burger bun, sautéed mushrooms, Swiss cheese, fries','de'=>'Rindfleisch-Patty, Burgerbrötchen, sautierte Pilze, Schweizer Käse, Pommes frites','ru'=>'говяжья котлета, булочка, обжаренные грибы, швейцарский сыр, картофель фри'], ['gluten','sut','susam'], 42, 72, 38),
    product('Sandviç & Burgerler', ['tr'=>'Çıtır Tavuk Burger','en'=>'Crispy Chicken Burger','de'=>'Knuspriger Chicken Burger','ru'=>'Бургер с хрустящей курицей'], ['tr'=>'çıtır tavuk, burger ekmeği, kırmızı lahana, marul, turşu, patates tava','en'=>'crispy chicken, burger bun, red cabbage, lettuce, pickles, fries','de'=>'knuspriges Hähnchen, Burgerbrötchen, Rotkohl, Salat, Essiggurken, Pommes frites','ru'=>'хрустящая курица, булочка, краснокочанная капуста, салат, соленья, картофель фри'], ['gluten','yumurta','susam'], 38, 78, 32),
    product('Sandviç & Burgerler', ['tr'=>'Mexican Burger','en'=>'Mexican Burger','de'=>'Mexican Burger','ru'=>'Мексиканский бургер'], ['tr'=>'dana köfte, burger ekmeği, jalapeño, dana bacon, çıtır soğan, patates tava','en'=>'beef patty, burger bun, jalapeño, beef bacon, crispy onion, fries','de'=>'Rindfleisch-Patty, Burgerbrötchen, Jalapeño, Rinderbacon, knusprige Zwiebeln, Pommes frites','ru'=>'говяжья котлета, булочка, халапеньо, говяжий бекон, хрустящий лук, картофель фри'], ['gluten','susam'], 44, 76, 40),
    product('Sandviç & Burgerler', ['tr'=>'Klasik Hamburger / Cheeseburger','en'=>'Classic Hamburger / Cheeseburger','de'=>'Klassischer Hamburger / Cheeseburger','ru'=>'Классический гамбургер / чизбургер'], ['tr'=>'dana köfte, burger ekmeği, marul, domates, soğan, turşu, cheddar, patates tava','en'=>'beef patty, burger bun, lettuce, tomato, onion, pickles, cheddar, fries','de'=>'Rindfleisch-Patty, Burgerbrötchen, Salat, Tomate, Zwiebel, Essiggurken, Cheddar, Pommes frites','ru'=>'говяжья котлета, булочка, салат, помидор, лук, соленья, чеддер, картофель фри'], ['gluten','sut','susam'], 42, 74, 36),
    product('Sandviç & Burgerler', ['tr'=>'Sosisli Sandviç','en'=>'Hot Dog','de'=>'Hotdog','ru'=>'Хот-дог'], ['tr'=>'sandviç ekmeği, ızgara sosis, cheddar peyniri, çıtır soğan, patates tava','en'=>'hot-dog bun, grilled sausage, cheddar, crispy onion, fries','de'=>'Hotdogbrötchen, Grillwurst, Cheddar, knusprige Zwiebeln, Pommes frites','ru'=>'булочка для хот-дога, колбаска на гриле, чеддер, хрустящий лук, картофель фри'], ['gluten','sut','susam'], 28, 82, 34),
    product('Makarnalar', ['tr'=>'Mac & Cheese','en'=>'Mac & Cheese','de'=>'Mac & Cheese','ru'=>'Макароны с сыром'], ['tr'=>'makarna, krema, cheddar peyniri, ekstra peynir','en'=>'pasta, cream, cheddar, extra cheese','de'=>'Pasta, Sahne, Cheddar, extra Käse','ru'=>'макароны, сливки, чеддер, дополнительный сыр'], ['gluten','sut'], 24, 78, 34),
    product('Makarnalar', ['tr'=>'Tagliatelle Limone & Gamberi','en'=>'Tagliatelle Limone & Gamberi','de'=>'Tagliatelle Limone & Gamberi','ru'=>'Тальятелле с лимоном и креветками'], ['tr'=>'yumurtalı tagliatelle, karides, limon, beyaz şarap, krema, roka','en'=>'egg tagliatelle, shrimp, lemon, white wine, cream, arugula','de'=>'Eier-Tagliatelle, Garnelen, Zitrone, Weißwein, Sahne, Rucola','ru'=>'яичная тальятелле, креветки, лимон, белое вино, сливки, руккола'], ['gluten','yumurta','kabuklu','sut','sulfit'], 34, 72, 24),
    product('Pide & Pizza', ['tr'=>'Gaziantep Lahmacun','en'=>'Gaziantep Lahmacun','de'=>'Gaziantep-Lahmacun','ru'=>'Лахмаджун по-газантепски'], ['tr'=>'ince hamur, dana kıyma, domates, yeşil biber, sarımsak, maydanoz, nar ekşisi','en'=>'thin dough, minced beef, tomato, green pepper, garlic, parsley, pomegranate molasses','de'=>'dünner Teig, Rinderhack, Tomate, grüne Paprika, Knoblauch, Petersilie, Granatapfelsirup','ru'=>'тонкое тесто, говяжий фарш, помидор, зелёный перец, чеснок, петрушка, гранатовый соус'], ['gluten'], 28, 58, 20),
    product('Pide & Pizza', ['tr'=>'Aydın Tulum Peynirli Pide','en'=>'Aydın Pide with Tulum Cheese','de'=>'Aydın-Pide mit Tulum-Käse','ru'=>'Пиде по-айдынски с сыром тулум'], ['tr'=>'pide hamuru, tulum peyniri, domates, taze kekik','en'=>'pide dough, Tulum cheese, tomato, fresh thyme','de'=>'Pide-Teig, Tulum-Käse, Tomate, frischer Thymian','ru'=>'тесто для пиде, сыр тулум, помидор, свежий тимьян'], ['gluten','sut'], 26, 76, 28),
    product('Pide & Pizza', ['tr'=>'İstanbul Karnabaharlı Pide','en'=>'Istanbul Cauliflower Pide','de'=>'Istanbuler Pide mit Blumenkohl','ru'=>'Стамбульское пиде с цветной капустой'], ['tr'=>'pide hamuru, karnabahar, kaşar peyniri, soğan, zeytinyağı','en'=>'pide dough, cauliflower, kashar cheese, onion, olive oil','de'=>'Pide-Teig, Blumenkohl, Kaşar-Käse, Zwiebel, Olivenöl','ru'=>'тесто для пиде, цветная капуста, сыр кашар, лук, оливковое масло'], ['gluten','sut'], 22, 80, 24),
    product('Pide & Pizza', ['tr'=>'Pesto Pomodorini Pizza','en'=>'Pesto Pomodorini Pizza','de'=>'Pesto-Pomodorini-Pizza','ru'=>'Пицца с песто и томатами черри'], ['tr'=>'pizza hamuru, pesto sos, mozzarella, cherry domates, fesleğen','en'=>'pizza dough, pesto, mozzarella, cherry tomatoes, basil','de'=>'Pizzateig, Pesto, Mozzarella, Kirschtomaten, Basilikum','ru'=>'тесто для пиццы, песто, моцарелла, помидоры черри, базилик'], ['gluten','sut','kuruyemis'], 24, 82, 30),
    product('Pide & Pizza', ['tr'=>'Quattro Stagioni Pizza','en'=>'Quattro Stagioni Pizza','de'=>'Pizza Quattro Stagioni','ru'=>'Пицца «Четыре сезона»'], ['tr'=>'pizza hamuru, mozzarella, mantar, dana jambon, zeytin, enginar','en'=>'pizza dough, mozzarella, mushrooms, beef ham, olives, artichoke','de'=>'Pizzateig, Mozzarella, Pilze, Rinderschinken, Oliven, Artischocke','ru'=>'тесто для пиццы, моцарелла, грибы, говяжья ветчина, оливки, артишок'], ['gluten','sut'], 30, 84, 32),
    product('Pide & Pizza', ['tr'=>'BBQ Chicken Pizza','en'=>'BBQ Chicken Pizza','de'=>'BBQ-Chicken-Pizza','ru'=>'Пицца с курицей BBQ'], ['tr'=>'pizza hamuru, mozzarella, barbekü soslu tavuk, kırmızı soğan, mısır, jalapeño','en'=>'pizza dough, mozzarella, BBQ chicken, red onion, corn, jalapeño','de'=>'Pizzateig, Mozzarella, BBQ-Hähnchen, rote Zwiebel, Mais, Jalapeño','ru'=>'тесто для пиццы, моцарелла, курица BBQ, красный лук, кукуруза, халапеньо'], ['gluten','sut','soya'], 36, 88, 30),
    product('Salatalar', ['tr'=>'Karadeniz Usulü Fasulye Diplemesi','en'=>'Black Sea-Style Green Bean Dip','de'=>'Bohnendip nach Schwarzmeer-Art','ru'=>'Дип из фасоли по-черноморски'], ['tr'=>'yeşil fasulye, domates, sarımsak, zeytinyağı, Karadeniz baharatları','en'=>'green beans, tomato, garlic, olive oil, Black Sea spices','de'=>'grüne Bohnen, Tomate, Knoblauch, Olivenöl, Schwarzmeer-Gewürze','ru'=>'зелёная фасоль, помидор, чеснок, оливковое масло, черноморские специи'], [], 8, 30, 16),
    product('Salatalar', ['tr'=>'Sağlık Salatası','en'=>'Wellness Salad','de'=>'Gesundheitssalat','ru'=>'Салат «Здоровье»'], ['tr'=>'greyfurt, brokoli, yeşil mercimek, roka, ceviz, zeytinyağı','en'=>'grapefruit, broccoli, green lentils, arugula, walnuts, olive oil','de'=>'Grapefruit, Brokkoli, grüne Linsen, Rucola, Walnüsse, Olivenöl','ru'=>'грейпфрут, брокколи, зелёная чечевица, руккола, грецкие орехи, оливковое масло'], ['kuruyemis'], 14, 42, 20),
    product('Salatalar', ['tr'=>'Balmy Salatası','en'=>'Balmy Salad','de'=>'Balmy-Salat','ru'=>'Салат Balmy'], ['tr'=>'semizotu, süzme yoğurt, pancar, lor peyniri, zeytinyağı','en'=>'purslane, strained yogurt, beetroot, curd cheese, olive oil','de'=>'Portulak, abgetropfter Joghurt, Rote Bete, Lor-Frischkäse, Olivenöl','ru'=>'портулак, густой йогурт, свёкла, творожный сыр, оливковое масло'], ['sut'], 16, 28, 18),
    product('Ana Yemekler', ['tr'=>'Ödemiş Köfte','en'=>'Ödemiş Meatballs','de'=>'Ödemiş-Köfte','ru'=>'Кёфте по-одемишски'], ['tr'=>'ızgara dana köfte, domates sosu, tereyağlı kızarmış köy ekmeği','en'=>'grilled beef meatballs, tomato sauce, buttered toasted rustic bread','de'=>'gegrillte Rinderköfte, Tomatensauce, geröstetes Landbrot mit Butter','ru'=>'говяжьи кёфте на гриле, томатный соус, поджаренный деревенский хлеб с маслом'], ['gluten','yumurta','sut'], 42, 46, 30),
    product('Ana Yemekler', ['tr'=>'Izgara Somon Fileto','en'=>'Grilled Salmon Fillet','de'=>'Gegrilltes Lachsfilet','ru'=>'Филе лосося на гриле'], ['tr'=>'somon fileto, kaya koruğu turşusu, sote patates, kapari sos','en'=>'salmon fillet, pickled rock samphire, sautéed potatoes, caper sauce','de'=>'Lachsfilet, eingelegter Felsensamphire, sautierte Kartoffeln, Kapernsauce','ru'=>'филе лосося, маринованный критмум, обжаренный картофель, соус с каперсами'], ['balik','sut'], 44, 42, 28),
    product('Ana Yemekler', ['tr'=>'Falafel Kaplamalı Tavuk But','en'=>'Falafel-Crusted Chicken Thigh','de'=>'Hähnchenkeule in Falafelkruste','ru'=>'Куриное бедро в корочке из фалафеля'], ['tr'=>'tavuk but, nohutlu falafel kaplama, fattoush salatası, tahinli limon sos','en'=>'chicken thigh, chickpea falafel crust, fattoush salad, tahini-lemon sauce','de'=>'Hähnchenkeule, Kichererbsen-Falafelkruste, Fattoush-Salat, Tahini-Zitronensauce','ru'=>'куриное бедро, корочка из нута и фалафеля, салат фаттуш, соус тахини с лимоном'], ['gluten','susam'], 42, 48, 26),
    product('Tatlılar', ['tr'=>'Limonlu Tart & Yanık Beze','en'=>'Lemon Tart & Torched Meringue','de'=>'Zitronentarte & flambierte Baiserhaube','ru'=>'Лимонный тарт с обожжённой меренгой'], ['tr'=>'tart hamuru, limon kreması, yumurta, tereyağı, yanık beze','en'=>'tart pastry, lemon curd, eggs, butter, torched meringue','de'=>'Tarteboden, Zitronencreme, Eier, Butter, flambiertes Baiser','ru'=>'песочная основа, лимонный крем, яйца, сливочное масло, обожжённая меренга'], ['gluten','yumurta','sut'], 8, 68, 24),
    product('Tatlılar', ['tr'=>'Lavantalı Crème Brûlée','en'=>'Lavender Crème Brûlée','de'=>'Lavendel-Crème-brûlée','ru'=>'Крем-брюле с лавандой'], ['tr'=>'krema, süt, yumurta sarısı, şeker, lavanta, vanilya','en'=>'cream, milk, egg yolk, sugar, lavender, vanilla','de'=>'Sahne, Milch, Eigelb, Zucker, Lavendel, Vanille','ru'=>'сливки, молоко, яичный желток, сахар, лаванда, ваниль'], ['yumurta','sut'], 8, 42, 28),
    product('Tatlılar', ['tr'=>'Hurmalı Cevizli Brownie','en'=>'Date & Walnut Brownie','de'=>'Brownie mit Datteln & Walnüssen','ru'=>'Брауни с финиками и грецкими орехами'], ['tr'=>'bitter çikolata, hurma, ceviz, un, yumurta, tereyağı, vanilyalı dondurma','en'=>'dark chocolate, dates, walnuts, flour, eggs, butter, vanilla ice cream','de'=>'Zartbitterschokolade, Datteln, Walnüsse, Mehl, Eier, Butter, Vanilleeis','ru'=>'тёмный шоколад, финики, грецкие орехи, мука, яйца, сливочное масло, ванильное мороженое'], ['gluten','yumurta','sut','kuruyemis','soya'], 10, 72, 30),
    product('7 Bölge 7 Lezzet', ['tr'=>'Kayseri Mantısı','en'=>'Kayseri Manti','de'=>'Kayseri-Manti','ru'=>'Кайсерийские манты'], ['tr'=>'minik mantı hamuru, dana kıyma, sarımsaklı yoğurt, tereyağlı pul biber sosu','en'=>'mini dumpling dough, minced beef, garlic yogurt, buttered red-pepper sauce','de'=>'kleine Teigtaschen, Rinderhack, Knoblauchjoghurt, Butter-Paprikasauce','ru'=>'маленькие пельмени, говяжий фарш, чесночный йогурт, масляный соус с красным перцем'], ['gluten','yumurta','sut'], 32, 72, 24),
    product('7 Bölge 7 Lezzet', ['tr'=>'Bursa İskender Kebap','en'=>'Bursa İskender Kebab','de'=>'Bursa-İskender-Kebab','ru'=>'Искендер-кебаб по-бурсски'], ['tr'=>'dana döner, pide ekmeği, domates sosu, yoğurt, tereyağı','en'=>'beef döner, pide bread, tomato sauce, yogurt, butter','de'=>'Rinderdöner, Pidebrot, Tomatensauce, Joghurt, Butter','ru'=>'говяжий донер, хлеб пиде, томатный соус, йогурт, сливочное масло'], ['gluten','sut'], 46, 68, 36),
    product('7 Bölge 7 Lezzet', ['tr'=>'Muş Keşkek','en'=>'Muş Keşkek','de'=>'Muş-Keşkek','ru'=>'Кешкек по-мушски'], ['tr'=>'dövme buğday, tavuk eti, tereyağlı sos, tuz, karabiber','en'=>'cracked wheat, chicken, butter sauce, salt, black pepper','de'=>'Weizenschrot, Hähnchen, Buttersauce, Salz, schwarzer Pfeffer','ru'=>'дроблёная пшеница, курица, сливочный соус, соль, чёрный перец'], ['gluten','sut'], 34, 74, 20),
    product('7 Bölge 7 Lezzet', ['tr'=>'Gaziantep Nohut Dürümü','en'=>'Gaziantep Chickpea Wrap','de'=>'Gaziantep-Kichererbsen-Wrap','ru'=>'Нутовый ролл по-газантепски'], ['tr'=>'lavaş, haşlanmış nohut, sumaklı soğan, maydanoz, mevsim yeşillikleri, baharatlar','en'=>'lavash, cooked chickpeas, sumac onion, parsley, seasonal greens, spices','de'=>'Lavash, gekochte Kichererbsen, Sumachzwiebeln, Petersilie, saisonale Blattsalate, Gewürze','ru'=>'лаваш, отварной нут, лук с сумахом, петрушка, сезонная зелень, специи'], ['gluten'], 18, 82, 14),
    product('7 Bölge 7 Lezzet', ['tr'=>'Sebzeli Zeytinyağlı Güveç','en'=>'Olive-Oil Vegetable Casserole','de'=>'Gemüsetopf mit Olivenöl','ru'=>'Овощное рагу с оливковым маслом'], ['tr'=>'patlıcan, kabak, biber, domates, soğan, sarımsak, zeytinyağı, taze otlar','en'=>'eggplant, zucchini, peppers, tomato, onion, garlic, olive oil, fresh herbs','de'=>'Aubergine, Zucchini, Paprika, Tomate, Zwiebel, Knoblauch, Olivenöl, frische Kräuter','ru'=>'баклажан, цукини, перец, помидор, лук, чеснок, оливковое масло, свежие травы'], [], 8, 38, 18),
    product('7 Bölge 7 Lezzet', ['tr'=>'Rize Karalahana Sarması','en'=>'Rize-Style Stuffed Kale','de'=>'Gefüllter Schwarzkohl nach Rize-Art','ru'=>'Голубцы из листовой капусты по-ризенски'], ['tr'=>'karalahana yaprağı, pirinç, soğan, domates salçası, zeytinyağı, Karadeniz baharatları','en'=>'kale leaves, rice, onion, tomato paste, olive oil, Black Sea spices','de'=>'Schwarzkohlblätter, Reis, Zwiebel, Tomatenmark, Olivenöl, Schwarzmeer-Gewürze','ru'=>'листья капусты, рис, лук, томатная паста, оливковое масло, черноморские специи'], [], 10, 62, 16),
    product('7 Bölge 7 Lezzet', ['tr'=>'Antalya Usulü Köfte & Piyaz','en'=>'Antalya-Style Meatballs & Bean Salad','de'=>'Antalya-Köfte & Bohnen-Piyaz','ru'=>'Кёфте и пияз по-анталийски'], ['tr'=>'ızgara dana köfte, kuru fasulye, tahin, yumurta, domates, soğan, maydanoz','en'=>'grilled beef meatballs, white beans, tahini, egg, tomato, onion, parsley','de'=>'gegrillte Rinderköfte, weiße Bohnen, Tahini, Ei, Tomate, Zwiebel, Petersilie','ru'=>'говяжьи кёфте на гриле, белая фасоль, тахини, яйцо, помидор, лук, петрушка'], ['gluten','yumurta','susam'], 44, 54, 28),
];

$run = in_array('--run', $argv, true);

$existingCategories = [];
foreach ($categories as $key => $definition) {
    $existingCategories[$key] = FoodCategory::where('branch_id', BRANCH_ID)
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [$key])
        ->first();
}

$existingProducts = 0;
foreach ($products as $item) {
    $existingProducts += FoodProduct::where('branch_id', BRANCH_ID)
        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [$item['title']['tr']])
        ->exists() ? 1 : 0;
}

echo 'Kategori: ' . count($categories) . ' (yeni: ' . count(array_filter($existingCategories, fn($v) => !$v)) . ')' . PHP_EOL;
echo 'Ürün: ' . count($products) . " (yeni: " . (count($products) - $existingProducts) . ", güncellenecek: {$existingProducts})" . PHP_EOL;

if (!$run) {
    echo "KURU KONTROL — değişiklik yapılmadı. Gerçek işlem için --run kullanın." . PHP_EOL;
    exit(0);
}

DB::transaction(function () use ($categories, $products): void {
    $categoryIds = [];

    foreach ($categories as $key => $definition) {
        $category = FoodCategory::where('branch_id', BRANCH_ID)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [$key])
            ->first();

        if (!$category) {
            $category = new FoodCategory(['branch_id' => BRANCH_ID]);
        }

        $mergedTitle = array_merge((array) ($category->title ?? []), $definition['title']);
        $category->fill([
            'branch_id' => BRANCH_ID,
            'title' => $mergedTitle,
            'icon' => $definition['icon'],
            'sort_order' => $definition['sort'],
            'is_active' => true,
        ])->save();
        $categoryIds[$key] = $category->id;
    }

    $sortByCategory = [];
    foreach ($products as $item) {
        $sortByCategory[$item['category']] = ($sortByCategory[$item['category']] ?? 0) + 1;

        $model = FoodProduct::where('branch_id', BRANCH_ID)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = ?", [$item['title']['tr']])
            ->first() ?? new FoodProduct();

        $model->fill([
            'branch_id' => BRANCH_ID,
            'food_category_id' => $categoryIds[$item['category']],
            'title' => $item['title'],
            'description' => $item['description'],
            'ingredients' => $item['ingredients'],
            'price' => 0,
            'badges' => null,
            'allergens' => $item['allergens'],
            'calories' => $item['calories'],
            'protein' => $item['protein'],
            'carbs' => $item['carbs'],
            'fat' => $item['fat'],
            'is_active' => true,
            'sort_order' => $sortByCategory[$item['category']],
        ])->save();
    }
});

echo "İçe aktarma tamamlandı." . PHP_EOL;
