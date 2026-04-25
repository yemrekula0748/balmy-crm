<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetHistory extends Model
{
    protected $fillable = [
        'asset_id', 'user_id', 'action', 'field', 'old_value', 'new_value', 'note',
    ];

    const FIELD_LABELS = [
        'name'          => 'Ad',
        'status'        => 'Durum',
        'location'      => 'Konum',
        'branch_id'     => 'Şube',
        'category_id'   => 'Kategori',
        'serial_no'     => 'Seri No',
        'warranty_until'=> 'Garanti Bitiş',
        'purchase_price'=> 'Alış Fiyatı',
        'purchase_date' => 'Alış Tarihi',
        'description'   => 'Açıklama',
        'asset_code'    => 'Demirbaş Kodu',
        'photo'         => 'Fotoğraf',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fieldLabel(): string
    {
        return self::FIELD_LABELS[$this->field] ?? $this->field ?? '';
    }
}
