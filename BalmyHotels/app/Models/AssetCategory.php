<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    protected $fillable = ['name', 'color', 'description', 'field_definitions'];

    protected $casts = [
        'field_definitions' => 'array',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    /**
     * field_definitions örnek yapısı:
     * [
     *   {"name":"marka","label":"Marka","type":"text","required":true},
     *   {"name":"model","label":"Model","type":"text","required":false},
     *   {"name":"garanti","label":"Garanti Bitiş","type":"date","required":false}
     * ]
     */
    public function getFieldTypes(): array
    {
        return ['text', 'number', 'date', 'select', 'textarea'];
    }
}
