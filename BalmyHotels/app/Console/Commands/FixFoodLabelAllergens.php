<?php

namespace App\Console\Commands;

use App\Models\FoodLabel;
use Illuminate\Console\Command;

class FixFoodLabelAllergens extends Command
{
    protected $signature   = 'food-labels:fix-allergens
                              {--dry-run : Değişiklik yapmadan sadece göster}
                              {--overwrite : Mevcut alerjen değerlerini tamamen üzerine yaz}
                              {--merge : Mevcut alerjenlere ek olarak tespit edilenleri de ekle (güvenli mod)}';
    protected $description = 'Yemek isimliklerinin içindekiler + isim metinlerini analiz ederek alerjen değerlerini otomasyonla günceller.';

    /** Anahtar → arama kelimeleri (küçük harf, TR dahil tüm diller) */
    private const KEYWORD_MAP = [

        // ── 🌾 GLUTEN ───────────────────────────────────────────────────────────
        'gluten' => [
            // TR – hamur & un ürünleri
            'un', 'buğday', 'bugday', 'arpa', 'çavdar', 'cavdar', 'yulaf', 'tritikale',
            'ekmek', 'bazlama', 'pide', 'simit', 'açma', 'acma', 'poğaça', 'pogaca',
            'lavaş', 'lavas', 'yufka', 'börek', 'borek', 'tepsi böreği', 'su böreği',
            'sigara böreği', 'milföy', 'milfoy', 'puf böreği', 'katmer',
            'hamur', 'hamurişi', 'pasta hamuru',
            'makarna', 'erişte', 'eriste', 'şehriye', 'sehriye', 'fide', 'kuskus',
            'bulgur', 'spaghetti', 'lazanya', 'tagliatelle', 'tagliatelli', 'fettuccine',
            'penne', 'rigatoni', 'fusilli', 'farfalle', 'ravioli', 'tortellini', 'agnolotti',
            'gnocchi', 'orzo', 'couscous', 'galeta', 'kraker', 'grissini', 'krakerler',
            'bisküvi', 'biskuvi', 'kurabiye', 'kek', 'pasta', 'tart', 'turta',
            'pandispanya', 'kadayıf', 'kadayif', 'baklava', 'börek', 'gözleme', 'gozleme',
            'krep', 'waffle', 'wafel', 'cornet', 'cone',
            'simit', 'pide', 'focaccia', 'bruschetta', 'crostini', 'crouton',
            'naan', 'chapati', 'tortilla', 'wrap', 'dürüm', 'durum',
            'pizza', 'calzone', 'stromboli',
            'çorba', 'corba', 'tarhana',
            // EN
            'flour', 'wheat', 'barley', 'rye', 'oat', 'spelt', 'kamut', 'triticale',
            'bread', 'roll', 'bun', 'bagel', 'muffin', 'scone', 'croissant',
            'pastry', 'pie crust', 'puff pastry', 'phyllo', 'filo',
            'pasta', 'noodle', 'dough', 'batter',
            'biscuit', 'cracker', 'breadcrumb', 'panko',
            'cake', 'brownie', 'wafer', 'cookie',
            'soy sauce', 'teriyaki', 'worcestershire',
            // DE
            'mehl', 'weizen', 'roggen', 'hafer', 'gerste', 'dinkel', 'emmer', 'einkorn',
            'brot', 'brötchen', 'semmel', 'brezel', 'laugengebäck',
            'teig', 'nudelteig', 'blätterteig', 'mürbeteig',
            'nudel', 'spaghetti', 'lasagne', 'knödel', 'spätzle',
            'semmelbrösel', 'paniermehl', 'keks', 'gebäck', 'torte', 'kuchen',
            // RU
            'мука', 'пшениц', 'рожь', 'ячмень', 'хлеб', 'булк', 'батон',
            'тесто', 'макарон', 'лапш', 'пельмен', 'вареник', 'блин', 'оладь',
            'печень', 'бисквит',
            // AR
            'دقيق', 'قمح', 'شعير', 'خبز', 'معكرون', 'عجين',
            // FR
            'farine', 'blé', 'seigle', 'avoine', 'orge', 'pain', 'brioche',
            'pâte', 'pâtes', 'gâteau', 'biscuit',
        ],

        // ── 🦐 KABUKLU DENİZ ÜRÜNLERİ ──────────────────────────────────────────
        'crustaceans' => [
            // TR
            'karides', 'ıstakoz', 'istakoz', 'yengeç', 'yengec', 'pavurya',
            'kerevit', 'langust', 'langosta', 'böcek ıstakoz',
            // EN
            'shrimp', 'prawn', 'lobster', 'crab', 'crayfish', 'crawfish',
            'scampi', 'langoustine', 'barnacle', 'krill',
            // DE
            'garnele', 'garnelen', 'hummer', 'krebs', 'krebse', 'krebstiere',
            'languste', 'scampi',
            // RU
            'креветк', 'лобстер', 'краб', 'раков', 'омар',
            // AR
            'روبيان', 'جمبري', 'كركند', 'سرطان البحر',
            // FR
            'crevette', 'homard', 'crabe', 'langouste', 'écrevisse',
        ],

        // ── 🥚 YUMURTA ───────────────────────────────────────────────────────────
        'eggs' => [
            // TR
            'yumurta', 'omlet', 'mayonez', 'hollandaise', 'béarnaise',
            'meringue', 'köpük', 'sufle', 'frittata', 'quiche', 'custard',
            'pane', 'paneleme',
            // EN
            'egg', 'eggs', 'mayonnaise', 'mayo', 'hollandaise', 'béarnaise',
            'meringue', 'soufflé', 'frittata', 'quiche', 'custard', 'curd',
            'albumin', 'lecithin',
            // DE
            'ei', 'eier', 'mayonnaise', 'eiweiss', 'eigelb', 'rührei',
            'spiegelei', 'omelett',
            // RU
            'яйц', 'яичн', 'майонез', 'омлет', 'яйца',
            // AR
            'بيض', 'مايونيز',
            // FR
            'oeuf', 'oeufs', 'mayonnaise', 'hollandaise', 'meringue',
        ],

        // ── 🐟 BALIK ─────────────────────────────────────────────────────────────
        'fish' => [
            // TR
            'balık', 'balik', 'somon', 'levrek', 'çipura', 'cipura',
            'ton balığı', 'ton', 'tuna', 'barbun', 'hamsi', 'istavrit',
            'palamut', 'torik', 'lüfer', 'lufer', 'kalkan', 'mezgit',
            'sardalya', 'uskumru', 'kılıç balığı', 'kilic baligi',
            'alabalık', 'alabalik', 'orkinos', 'çinekop', 'lüfer',
            'karagöz', 'sinarit', 'fangri', 'lagos', 'mercan',
            'izmarit', 'kurbağa balığı', 'pisi', 'dil balığı', 'dil baligi',
            'havyar', 'ikra', 'ançuez', 'ancuez', 'hamsi ezmesi',
            'balık sosu', 'balık suyu', 'balık unu',
            // EN
            'fish', 'salmon', 'sea bass', 'anchovy', 'anchovies', 'trout',
            'cod', 'tuna', 'mackerel', 'sardine', 'herring', 'tilapia',
            'halibut', 'flounder', 'sole', 'perch', 'pike', 'catfish',
            'swordfish', 'marlin', 'bass', 'bream', 'carp',
            'caviar', 'roe', 'fish sauce', 'fish stock', 'worcestershire',
            // DE
            'lachs', 'fisch', 'forelle', 'thunfisch', 'sardine', 'hering',
            'dorsch', 'kabeljau', 'scholle', 'seezunge', 'wolfsbarsch',
            'dorade', 'schwertfisch', 'aal', 'kaviar', 'fischsauce',
            // RU
            'рыб', 'лосос', 'форел', 'окун', 'треск', 'тунец', 'семг',
            'шпрот', 'сельд', 'скумбр', 'икра', 'анчоус',
            // AR
            'سمك', 'سلمون', 'تونا', 'سردين', 'أنشوجة',
            // FR
            'poisson', 'saumon', 'thon', 'anchois', 'truite', 'morue',
            'cabillaud', 'hareng', 'sardine', 'maquereau',
        ],

        // ── 🥜 YER FISTIĞI ───────────────────────────────────────────────────────
        'peanuts' => [
            // TR
            'yer fıstığı', 'yer fistigi', 'yerfıstığı', 'yerfistigi',
            'fıstık ezmesi', 'fistik ezmesi', 'f.ezmesi',
            'arachide',
            // EN
            'peanut', 'peanuts', 'groundnut', 'groundnuts',
            'peanut butter', 'peanut oil', 'monkey nuts', 'arachis',
            // DE
            'erdnuss', 'erdnüsse', 'erdnussbutter', 'erdnussöl',
            // RU
            'арахис', 'земляной орех',
            // AR
            'فول سوداني',
            // FR
            'arachide', 'cacahuète', 'cacahuètes',
        ],

        // ── 🫘 SOYA ──────────────────────────────────────────────────────────────
        'soybeans' => [
            // TR
            'soya', 'soya fasulyesi', 'soya sosu', 'tofu', 'tempeh', 'miso',
            'edamame', 'soya yağı', 'soya proteini', 'soya unu',
            // EN
            'soy', 'soya', 'soybean', 'soybeans', 'tofu', 'tempeh', 'miso',
            'edamame', 'soy sauce', 'soy milk', 'soy protein', 'soy flour',
            'tamari', 'natto',
            // DE
            'soja', 'sojabohne', 'sojasoße', 'sojamilch', 'tofu', 'miso',
            // RU
            'соя', 'соевый', 'тофу', 'мисо', 'темпе',
            // AR
            'فول الصويا', 'صويا',
            // FR
            'soja', 'tofu', 'miso', 'tempeh', 'sauce soja',
        ],

        // ── 🥛 SÜT / LAKTOZ ─────────────────────────────────────────────────────
        'milk' => [
            // TR
            'süt', 'sut', 'tereyağı', 'tereyagi', 'yağ', 'margarin',
            'peynir', 'kaşar', 'kasar', 'tulum', 'beyaz peynir', 'çökelek',
            'cokelek', 'lor', 'ricotta', 'mozzarella', 'parmesan', 'parmigiano',
            'grana padano', 'pecorino', 'gorgonzola', 'brie', 'camembert',
            'cheddar', 'gouda', 'gruyere', 'emmental', 'roquefort',
            'feta', 'halloumi', 'labne', 'krem peynir', 'cottage',
            'mascarpone', 'scamorza', 'burrata', 'fontina',
            'yoğurt', 'yogurt', 'süzme yoğurt', 'ayran', 'kefir',
            'krema', 'kaymak', 'süt kreması', 'çırpılmış krema',
            'dondurma', 'sorbe', 'dövme',
            'muhallebi', 'sütlaç', 'sutlac', 'güllaç', 'gullas',
            'béchamel', 'beshamel', 'beşamel', 'besamel',
            'cacık', 'cacik', 'tzatziki', 'haydari',
            'tereyağlı', 'sütlü',
            // EN
            'milk', 'dairy', 'lactose', 'butter', 'cheese', 'cream',
            'yoghurt', 'yogurt', 'whey', 'casein', 'lactate',
            'ice cream', 'gelato', 'half-and-half', 'buttermilk',
            'sour cream', 'crème fraîche', 'creme fraiche',
            'condensed milk', 'evaporated milk', 'skimmed milk', 'full fat milk',
            'béchamel', 'bechamel', 'mornay',
            'custard', 'panna cotta', 'pannacotta',
            // DE
            'milch', 'butter', 'sahne', 'rahm', 'käse', 'quark', 'joghurt',
            'frischkäse', 'schlagsahne', 'sauerrahm', 'buttermilch',
            'kondensmilch', 'milchpulver', 'molke', 'kasein', 'laktose',
            'eis', 'eiscreme', 'pannacotta', 'pudding',
            // RU
            'молоко', 'сливк', 'масл', 'сметан', 'сыр', 'творог',
            'кефир', 'йогурт', 'ряженк', 'мороженое', 'сгущ',
            'казеин', 'лактоз',
            // AR
            'حليب', 'جبن', 'زبدة', 'قشدة', 'لبن', 'لاكتوز', 'كريمة',
            // FR
            'lait', 'beurre', 'crème', 'fromage', 'yaourt', 'lactose',
            'whey', 'caséine', 'glace',
        ],

        // ── 🌰 KABUKLU YEMİŞ ────────────────────────────────────────────────────
        'nuts' => [
            // TR – genel
            'kabuklu yemiş', 'kuruyemiş',
            // TR – çeşitler
            'badem', 'ceviz', 'fındık', 'findik', 'antep fıstığı', 'antep fistigi',
            'antepfıstığı', 'antepfistigi', 'Antep fıstık', 'fıstık',
            'kaju', 'kešu', 'pekan', 'macadamia', 'kestane',
            'brezilya fıstığı', 'brezilya fistigi',
            'çam fıstığı', 'cam fistigi', 'çamfıstığı',
            'krokant', 'pralin', 'marzipan', 'nougat',
            'badem ezmesi', 'badem sütü', 'badempasta',
            'tahinli', // susam ayrıca var ama kuruyemiş ezmesi olarak da kullanılır
            // EN
            'nut', 'nuts', 'almond', 'walnut', 'hazelnut', 'pistachio',
            'cashew', 'pecan', 'macadamia', 'brazil nut', 'pine nut',
            'chestnut', 'praline', 'marzipan', 'nougat',
            'almond milk', 'almond flour', 'almond paste',
            // DE
            'nuss', 'nüsse', 'mandel', 'mandeln', 'walnuss', 'walnüsse',
            'haselnuss', 'haselnüsse', 'pistazie', 'pistazien',
            'cashew', 'pekan', 'macadamia', 'maroni', 'kastanie',
            'mandelmehl', 'marzipan', 'nougat', 'krokant', 'praline',
            // RU
            'орех', 'орехи', 'миндал', 'фундук', 'грецк', 'фисташ',
            'кешью', 'пекан', 'каштан', 'пралин', 'марципан', 'нуга',
            // AR
            'مكسرات', 'لوز', 'جوز', 'بندق', 'فستق', 'كاجو',
            // FR
            'noix', 'noisette', 'amande', 'pistache', 'cajou', 'pécan',
            'macadamia', 'châtaigne', 'praline', 'nougat', 'massepain',
        ],

        // ── 🥬 KEREVİZ ───────────────────────────────────────────────────────────
        'celery' => [
            // TR
            'kereviz', 'kereviz sapı', 'kereviz kökü', 'kereviz tohumu',
            // EN
            'celery', 'celeriac', 'celery seed', 'celery salt',
            // DE
            'sellerie', 'knollensellerie', 'staudensellerie', 'selleriesalz',
            // RU
            'сельдерей',
            // AR
            'كرفس',
            // FR
            'céleri', 'céleri-rave', 'céleri branche',
        ],

        // ── 🌿 HARDAL ────────────────────────────────────────────────────────────
        'mustard' => [
            // TR
            'hardal', 'hardal tohumu', 'hardal sosu', 'hardal yağı',
            // EN
            'mustard', 'mustard seed', 'mustard oil', 'mustard flour', 'dijon',
            'wholegrain mustard', 'english mustard', 'american mustard',
            // DE
            'senf', 'senfkörner', 'senfmehl', 'senfsauce', 'dijonsenf',
            // RU
            'горчиц',
            // AR
            'خردل',
            // FR
            'moutarde', 'graine de moutarde',
        ],

        // ── ⚪ SUSAM ──────────────────────────────────────────────────────────────
        'sesame' => [
            // TR
            'susam', 'tahin', 'tahini', 'halva', 'helva', 'susam yağı',
            'susamlı', 'tahinli', 'susamlı çubuk',
            // EN
            'sesame', 'tahini', 'sesame oil', 'sesame seed', 'halva', 'hummus',
            // DE
            'sesam', 'sesamöl', 'sesamkörner', 'tahini', 'halwa',
            // RU
            'кунжут', 'тахин', 'халва',
            // AR
            'سمسم', 'طحينة', 'حلوى',
            // FR
            'sésame', 'tahini', 'huile de sésame', 'halva',
        ],

        // ── 🍷 SULFİTLER ─────────────────────────────────────────────────────────
        'sulphites' => [
            // TR
            'şarap', 'sarap', 'kırmızı şarap', 'beyaz şarap', 'rosé',
            'şampanya', 'sampanya', 'prosecco', 'cava', 'sekt',
            'balzamik', 'balsamic', 'balzamik sirke', 'şarap sirkesi',
            'kuru üzüm', 'kuru uzum', 'kurutulmuş kayısı', 'kurutulmuş meyve',
            'konserve', 'turşu', 'pickle', 'salamura',
            'sülfür dioksit', 'sulfit',
            // EN
            'wine', 'red wine', 'white wine', 'sparkling wine',
            'champagne', 'prosecco', 'port', 'sherry', 'vermouth',
            'balsamic', 'balsamic vinegar', 'wine vinegar',
            'dried fruit', 'dried apricot', 'raisin', 'sultana',
            'sulphite', 'sulfite', 'sulphur dioxide', 'so2',
            'pickled', 'preserved',
            // DE
            'wein', 'rotwein', 'weißwein', 'sekt', 'champagner', 'prosecco',
            'balsamico', 'balsamicoessig', 'weinessig', 'rosinen',
            'sulfit', 'schwefeldioxid',
            // RU
            'вино', 'красное вино', 'белое вино', 'шампанск', 'бальзамик',
            'сульфит', 'диоксид серы', 'изюм',
            // AR
            'نبيذ', 'كبريتيت', 'فواكه مجففة',
            // FR
            'vin', 'champagne', 'vinaigre balsamique', 'fruits secs',
            'sulfite', 'dioxyde de soufre',
        ],

        // ── 🌼 LUPIN (ACI BAKLA) ─────────────────────────────────────────────────
        'lupin' => [
            // TR
            'acı bakla', 'aci bakla', 'lupine', 'lupin', 'bakla unu',
            // EN
            'lupin', 'lupine', 'lupin flour', 'lupin seed', 'lupin bean',
            // DE
            'lupine', 'lupinen', 'lupinenmehl', 'lupinensamen',
            // RU
            'люпин',
            // AR
            'الترمس',
            // FR
            'lupin', 'farine de lupin',
        ],

        // ── 🐚 YUMUŞAKÇALAR ─────────────────────────────────────────────────────
        'molluscs' => [
            // TR
            'ahtapot', 'kalamar', 'mürekkep balığı', 'murekkep baligi',
            'midye', 'istiridye', 'istakoz', 'tarak', 'salyangoz',
            'deniz tarağı', 'deniz taragi', 'karabiber midye', 'kalamar halkası',
            // EN
            'squid', 'octopus', 'oyster', 'mussel', 'clam', 'snail',
            'scallop', 'abalone', 'cuttlefish', 'whelk', 'periwinkle',
            'squid ink', 'octopus', 'cephalopod', 'mollusc', 'mollusk',
            'oyster sauce',
            // DE
            'tintenfisch', 'oktopus', 'muschel', 'auster', 'jakobsmuschel',
            'schnecke', 'kalmar', 'tintenfischsauce',
            // RU
            'моллюск', 'кальмар', 'осьминог', 'мидий', 'устриц', 'улитк',
            'гребешок',
            // AR
            'حبار', 'أخطبوط', 'محار', 'بلح البحر', 'حلزون',
            // FR
            'calmar', 'poulpe', 'huître', 'moule', 'escargot', 'pétoncle',
            'seiche', 'encornet',
        ],
    ];

    public function handle(): int
    {
        $dryRun   = $this->option('dry-run');
        $overwrite = $this->option('overwrite');
        $merge     = $this->option('merge');

        $labels = FoodLabel::all();
        $this->info("Toplam {$labels->count()} kayıt analiz ediliyor...");

        $updated = 0;
        $skipped = 0;

        foreach ($labels as $label) {
            $existing = $label->allergens ?? [];

            // Varsayılan: sadece boş olanları doldur; --merge: eksik ekle; --overwrite: tamamen yaz
            if (!$overwrite && !$merge && count($existing) > 0) {
                $skipped++;
                continue;
            }

            $text     = $this->buildText($label);
            $detected = $this->detectAllergens($text);

            if ($overwrite) {
                $merged = $detected;
            } elseif ($merge) {
                $merged = array_values(array_unique(array_merge($existing, $detected)));
            } else {
                $merged = $detected;
            }

            sort($merged);
            $existingSorted = $existing;
            sort($existingSorted);

            if ($merged === $existingSorted) {
                $skipped++;
                continue;
            }

            $name = is_array($label->name) ? ($label->name['tr'] ?? $label->name['en'] ?? '') : $label->name;
            $this->line(sprintf(
                '<fg=yellow>%d: %s</> | eskiden: <fg=red>[%s]</> → yeni: <fg=green>[%s]</>',
                $label->id,
                mb_substr($name, 0, 40),
                implode(', ', $existingSorted),
                implode(', ', $merged)
            ));

            if (!$dryRun) {
                $label->allergens = $merged;
                $label->save();
            }
            $updated++;
        }

        $action = $dryRun ? '(DRY-RUN) güncellenecek' : 'güncellendi';
        $this->newLine();
        $this->info("Tamamlandı. {$updated} kayıt {$action}, {$skipped} atlandı.");

        if ($dryRun) {
            $this->comment('Gerçekten uygulamak için --overwrite veya boş olanlar için: php artisan food-labels:fix-allergens');
        }

        return self::SUCCESS;
    }

    private function buildText(FoodLabel $label): string
    {
        $parts = [];

        // İsim (tüm diller)
        foreach ((array)($label->name ?? []) as $v) {
            $parts[] = is_string($v) ? $v : '';
        }

        // Açıklama (tüm diller)
        foreach ((array)($label->description ?? []) as $v) {
            $parts[] = is_string($v) ? $v : '';
        }

        // İçindekiler (tüm diller)
        $ing = $label->ingredients ?? [];
        foreach ((array)$ing as $v) {
            if (is_array($v)) {
                $parts[] = implode(', ', $v);
            } elseif (is_string($v)) {
                $parts[] = $v;
            }
        }

        return mb_strtolower(implode(' ', array_filter($parts)));
    }

    private function detectAllergens(string $text): array
    {
        $found = [];
        foreach (self::KEYWORD_MAP as $allergen => $keywords) {
            foreach ($keywords as $kw) {
                $kw = mb_strtolower($kw);
                // Çok kelimeli ifadeler için düz arama, tek kelimeler için \b sınırı
                if (mb_strpos($kw, ' ') !== false) {
                    $match = mb_strpos($text, $kw) !== false;
                } else {
                    $pattern = '/\b' . preg_quote($kw, '/') . '\b/u';
                    $match   = (bool) preg_match($pattern, $text);
                }
                if ($match) {
                    $found[] = $allergen;
                    break;
                }
            }
        }
        return $found;
    }
}
