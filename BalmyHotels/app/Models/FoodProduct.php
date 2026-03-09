<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodProduct extends Model
{
    protected $fillable = [
        'branch_id', 'food_category_id',
        'title', 'description', 'price',
        'image', 'badges', 'allergens', 'ingredients', 'options',
        'calories', 'protein', 'carbs', 'fat',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'title'       => 'array',
        'description' => 'array',
        'badges'      => 'array',
        'allergens'   => 'array',
        'ingredients' => 'array',
        'options'     => 'array',
        'is_active'   => 'boolean',
        'price'       => 'float',
        'calories'    => 'float',
        'protein'     => 'float',
        'carbs'       => 'float',
        'fat'         => 'float',
    ];

    /** 14 AB Alerjen sabiti [key => [tr, en, emoji]] */
    const ALLERGENS = [
        'gluten'      => ['tr' => 'Gluten',                 'en' => 'Gluten',           'de' => 'Gluten',            'ru' => 'Глютен',              'ar' => 'الجلوتين',       'fr' => 'Gluten',              'emoji' => '🌾'],
        'kabuklu'     => ['tr' => 'Kabuklu Deniz Ürünleri', 'en' => 'Crustaceans',      'de' => 'Krebstiere',        'ru' => 'Ракообразные',        'ar' => 'القشريات',       'fr' => 'Crustacés',           'emoji' => '🦐'],
        'yumurta'     => ['tr' => 'Yumurta',                'en' => 'Eggs',             'de' => 'Eier',              'ru' => 'Яйца',                'ar' => 'البيض',          'fr' => 'Œufs',                'emoji' => '🥚'],
        'balik'       => ['tr' => 'Balık',                  'en' => 'Fish',             'de' => 'Fisch',             'ru' => 'Рыба',                'ar' => 'السمك',          'fr' => 'Poisson',             'emoji' => '🐟'],
        'fistik'      => ['tr' => 'Yer Fıstığı',            'en' => 'Peanuts',          'de' => 'Erdnüsse',          'ru' => 'Арахис',              'ar' => 'الفول السوداني', 'fr' => 'Arachides',           'emoji' => '🥜'],
        'soya'        => ['tr' => 'Soya',                   'en' => 'Soybeans',         'de' => 'Sojabohnen',        'ru' => 'Соя',                 'ar' => 'فول الصويا',     'fr' => 'Soja',                'emoji' => '🫘'],
        'sut'         => ['tr' => 'Süt / Laktoz',           'en' => 'Milk',             'de' => 'Milch / Laktose',   'ru' => 'Молоко / Лактоза',   'ar' => 'الحليب/اللاكتوز','fr' => 'Lait / Lactose',      'emoji' => '🥛'],
        'kuruyemis'   => ['tr' => 'Kuruyemiş',              'en' => 'Nuts',             'de' => 'Schalenfrüchte',    'ru' => 'Орехи',               'ar' => 'المكسرات',       'fr' => 'Fruits à coque',      'emoji' => '🌰'],
        'kereviz'     => ['tr' => 'Kereviz',                'en' => 'Celery',           'de' => 'Sellerie',          'ru' => 'Сельдерей',           'ar' => 'الكرفس',         'fr' => 'Céleri',              'emoji' => '🥬'],
        'hardal'      => ['tr' => 'Hardal',                 'en' => 'Mustard',          'de' => 'Senf',              'ru' => 'Горчица',             'ar' => 'الخردل',         'fr' => 'Moutarde',            'emoji' => '🌿'],
        'susam'       => ['tr' => 'Susam',                  'en' => 'Sesame',           'de' => 'Sesam',             'ru' => 'Кунжут',              'ar' => 'السمسم',         'fr' => 'Sésame',              'emoji' => '🫙'],
        'sulfit'      => ['tr' => 'Sülfitler',              'en' => 'Sulphites',        'de' => 'Sulfite',           'ru' => 'Сульфиты',            'ar' => 'الكبريتيت',      'fr' => 'Sulfites',            'emoji' => '🍷'],
        'acibakla'    => ['tr' => 'Acı Bakla (Lupine)',     'en' => 'Lupin',            'de' => 'Lupinen',           'ru' => 'Люпин',               'ar' => 'الترمس',         'fr' => 'Lupin',               'emoji' => '🌼'],
        'yumusakcalar'=> ['tr' => 'Yumuşakçalar',           'en' => 'Molluscs',         'de' => 'Weichtiere',        'ru' => 'Моллюски',            'ar' => 'الرخويات',       'fr' => 'Mollusques',          'emoji' => '🐚'],
    ];

    /** Rozet seçenekleri — QrMenuItem ile aynı */
    const BADGE_OPTIONS = [
        'Vegan', 'Vejeteryan', 'Glutensiz', 'Laktozsuz',
        'Acılı', 'Önerilen', 'Yeni', 'Popüler',
    ];

    const BADGE_COLORS = [
        'Vegan'      => '#2d6a4f',
        'Vejeteryan' => '#40916c',
        'Glutensiz'  => '#e9c46a',
        'Laktozsuz'  => '#f4a261',
        'Acılı'      => '#e63946',
        'Önerilen'   => '#c19b77',
        'Yeni'       => '#457b9d',
        'Popüler'    => '#9b2226',
    ];

    /** Dinamik opsiyon tipleri */
    const OPTION_TYPES = [
        'text'   => 'Metin',
        'number' => 'Sayı',
        'tags'   => 'Etiket listesi',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function foodCategory(): BelongsTo
    {
        return $this->belongsTo(FoodCategory::class);
    }

    /** Bu üründen türetilen menü kalemleri */
    public function menuItems(): HasMany
    {
        return $this->hasMany(QrMenuItem::class, 'food_product_id');
    }

    public function getTitle(string $lang = 'tr'): string
    {
        $titles = array_filter((array)($this->title ?? []), fn($v) => is_string($v) && $v !== '');
        return $titles[$lang] ?? array_values($titles)[0] ?? '—';
    }

    public function getDescription(string $lang = 'tr'): string
    {
        $descs = array_filter((array)($this->description ?? []), fn($v) => is_string($v) && $v !== '');
        return $descs[$lang] ?? array_values($descs)[0] ?? '';
    }

    public function formattedPrice(?string $symbol = '₺'): string
    {
        if ($this->price === null) return '';
        return $symbol . ' ' . number_format($this->price, 2);
    }
}
