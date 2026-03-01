<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodLabel extends Model
{
    protected $fillable = [
        'branch_id', 'created_by', 'name', 'description', 'ingredients',
        'calories', 'allergens', 'category',
        'is_vegan', 'is_vegetarian', 'is_halal', 'is_active', 'sort_order',
    ];

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
        'gluten'      => ['label' => 'Gluten',             'label_en' => 'Gluten',         'icon' => '🌾', 'eu' => 1],
        'crustaceans' => ['label' => 'Kabuklu Deniz Ür.',  'label_en' => 'Crustaceans',    'icon' => '🦐', 'eu' => 2],
        'eggs'        => ['label' => 'Yumurta',            'label_en' => 'Eggs',           'icon' => '🥚', 'eu' => 3],
        'fish'        => ['label' => 'Balık',              'label_en' => 'Fish',           'icon' => '🐟', 'eu' => 4],
        'peanuts'     => ['label' => 'Yer Fıstığı',        'label_en' => 'Peanuts',        'icon' => '🥜', 'eu' => 5],
        'soybeans'    => ['label' => 'Soya',               'label_en' => 'Soybeans',       'icon' => '🫘', 'eu' => 6],
        'milk'        => ['label' => 'Süt / Laktoz',       'label_en' => 'Milk',           'icon' => '🥛', 'eu' => 7],
        'nuts'        => ['label' => 'Kabuklu Yemiş',      'label_en' => 'Nuts',           'icon' => '🌰', 'eu' => 8],
        'celery'      => ['label' => 'Kereviz',            'label_en' => 'Celery',         'icon' => '🥬', 'eu' => 9],
        'mustard'     => ['label' => 'Hardal',             'label_en' => 'Mustard',        'icon' => '🌿', 'eu' => 10],
        'sesame'      => ['label' => 'Susam',              'label_en' => 'Sesame',         'icon' => '⚪', 'eu' => 11],
        'sulphites'   => ['label' => 'Sülfitler',          'label_en' => 'Sulphites',      'icon' => '🍷', 'eu' => 12],
        'lupin'       => ['label' => 'Acı Bakla',          'label_en' => 'Lupin',          'icon' => '🫛', 'eu' => 13],
        'molluscs'    => ['label' => 'Yumuşakçalar',       'label_en' => 'Molluscs',       'icon' => '🐚', 'eu' => 14],
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
