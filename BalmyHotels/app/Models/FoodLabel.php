<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FoodLabel extends Model
{
    protected $fillable = [
        'branch_id', 'created_by', 'qr_token', 'name', 'description', 'ingredients',
        'calories', 'allergens', 'category',
        'is_vegan', 'is_vegetarian', 'is_halal', 'is_active', 'sort_order',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->qr_token)) {
                $model->qr_token = (string) Str::uuid();
            }
        });
    }

    protected $casts = [
        'name'         => 'array',
        'description'  => 'array',
        'ingredients'  => 'array',
        'allergens'    => 'array',
        'is_vegan'     => 'boolean',
        'is_vegetarian'=> 'boolean',
        'is_halal'     => 'boolean',
        'is_active'    => 'boolean',
    ];

    // -----------------------------------------------------------------------
    // AB standartı 14 allerjen
    // -----------------------------------------------------------------------
    const ALLERGENS = [
        'gluten'      => ['label' => 'Gluten',             'label_en' => 'Gluten',         'label_de' => 'Gluten',          'label_ru' => 'Глютен',           'icon' => '🌾', 'eu' => 1],
        'crustaceans' => ['label' => 'Kabuklu Deniz Ür.',  'label_en' => 'Crustaceans',    'label_de' => 'Krebstiere',      'label_ru' => 'Ракообразные',     'icon' => '🦐', 'eu' => 2],
        'eggs'        => ['label' => 'Yumurta',            'label_en' => 'Eggs',           'label_de' => 'Eier',            'label_ru' => 'Яйца',             'icon' => '🥚', 'eu' => 3],
        'fish'        => ['label' => 'Balık',              'label_en' => 'Fish',           'label_de' => 'Fisch',           'label_ru' => 'Рыба',             'icon' => '🐟', 'eu' => 4],
        'peanuts'     => ['label' => 'Yer Fıstığı',        'label_en' => 'Peanuts',        'label_de' => 'Erdnüsse',        'label_ru' => 'Арахис',           'icon' => '🥜', 'eu' => 5],
        'soybeans'    => ['label' => 'Soya',               'label_en' => 'Soybeans',       'label_de' => 'Soja',            'label_ru' => 'Соя',              'icon' => '🫘', 'eu' => 6],
        'milk'        => ['label' => 'Süt / Laktoz',       'label_en' => 'Milk',           'label_de' => 'Milch',           'label_ru' => 'Молоко',           'icon' => '🥛', 'eu' => 7],
        'nuts'        => ['label' => 'Kabuklu Yemiş',      'label_en' => 'Nuts',           'label_de' => 'Schalenfrüchte',  'label_ru' => 'Орехи',            'icon' => '🌰', 'eu' => 8],
        'celery'      => ['label' => 'Kereviz',            'label_en' => 'Celery',         'label_de' => 'Sellerie',        'label_ru' => 'Сельдерей',        'icon' => '🥬', 'eu' => 9],
        'mustard'     => ['label' => 'Hardal',             'label_en' => 'Mustard',        'label_de' => 'Senf',            'label_ru' => 'Горчица',          'icon' => '🌿', 'eu' => 10],
        'sesame'      => ['label' => 'Susam',              'label_en' => 'Sesame',         'label_de' => 'Sesam',           'label_ru' => 'Кунжут',           'icon' => '⚪', 'eu' => 11],
        'sulphites'   => ['label' => 'Sülfitler',          'label_en' => 'Sulphites',      'label_de' => 'Sulfite',         'label_ru' => 'Сульфиты',         'icon' => '🍷', 'eu' => 12],
        'lupin'       => ['label' => 'Acı Bakla',          'label_en' => 'Lupin',          'label_de' => 'Lupinen',         'label_ru' => 'Люпин',            'icon' => '🫛', 'eu' => 13],
        'molluscs'    => ['label' => 'Yumuşakçalar',       'label_en' => 'Molluscs',       'label_de' => 'Weichtiere',      'label_ru' => 'Моллюски',         'icon' => '🐚', 'eu' => 14],
    ];

    const LANGUAGES = [
        'tr' => ['name' => 'Türkçe',    'flag' => '🇹🇷'],
        'en' => ['name' => 'English',   'flag' => '🇬🇧'],
        'de' => ['name' => 'Deutsch',   'flag' => '🇩🇪'],
        'ru' => ['name' => 'Русский',   'flag' => '🇷🇺'],
        'ar' => ['name' => 'العربية',  'flag' => '🇸🇦'],
        'fr' => ['name' => 'Français',  'flag' => '🇫🇷'],
    ];

    const CATEGORIES = [
        'soup'      => 'Çorba',
        'salad'     => 'Salata',
        'appetizer' => 'Meze / Başlangıç',
        'main'      => 'Ana Yemek',
        'side'      => 'Yan Yemek',
        'dessert'   => 'Tatlı',
        'beverage'  => 'İçecek',
        'breakfast' => 'Kahvaltı',
        'other'     => 'Diğer',
    ];

    // -----------------------------------------------------------------------
    public function branch()  { return $this->belongsTo(Branch::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    /** Misafirin QR okutunca gideceği public URL */
    public function publicUrl(): string
    {
        return route('food-labels.public', $this->qr_token);
    }

    public function getName(string $lang = 'tr'): string
    {
        $n = $this->name ?? [];
        return $n[$lang] ?? $n['tr'] ?? $n['en'] ?? (count($n) ? array_values($n)[0] : '') ?: '';
    }

    public function getDescription(string $lang = 'tr'): string
    {
        $d = $this->description ?? [];
        return $d[$lang] ?? $d['tr'] ?? $d['en'] ?? '' ?: '';
    }

    public function getIngredients(string $lang = 'tr'): array
    {
        $i = $this->ingredients ?? [];
        return $i[$lang] ?? $i['tr'] ?? $i['en'] ?? [];
    }

    public function getCategoryLabel(): string
    {
        return self::CATEGORIES[$this->category ?? ''] ?? ($this->category ?? '');
    }

    public function getAllergenList(): array
    {
        $keys = $this->allergens ?? [];
        return array_intersect_key(self::ALLERGENS, array_flip($keys));
    }
}
