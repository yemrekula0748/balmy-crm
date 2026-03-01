<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    const STATUSES = [
        'available'   => 'Mevcut',
        'in_use'      => 'Kullanımda',
        'maintenance' => 'Bakımda',
        'retired'     => 'Hizmet Dışı',
    ];

    const STATUS_COLORS = [
        'available'   => 'success',
        'in_use'      => 'warning',
        'maintenance' => 'info',
        'retired'     => 'secondary',
    ];

    const STATUS_ICONS = [
        'available'   => '✓',
        'in_use'      => '→',
        'maintenance' => '⚙',
        'retired'     => '✕',
    ];

    protected $fillable = [
        'asset_code', 'category_id', 'branch_id', 'name', 'description',
        'location', 'status', 'purchase_date', 'purchase_price',
        'serial_no', 'warranty_until', 'photo', 'properties',
    ];

    protected $casts = [
        'properties'    => 'array',
        'purchase_date' => 'date',
        'warranty_until'=> 'date',
        'purchase_price'=> 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function exits(): HasMany
    {
        return $this->hasMany(AssetExit::class);
    }

    public function activeExit(): ?AssetExit
    {
        return $this->exits()
            ->whereIn('status', ['approved'])
            ->whereNull('returned_at')
            ->latest()
            ->first();
    }

    /**
     * Bir sonraki demirbaş kodu üret: DMB-0001 formatı
     */
    public static function generateCode(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'DMB-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Garantisi dolmuş mu?
     */
    public function isWarrantyExpired(): bool
    {
        return $this->warranty_until && $this->warranty_until->isPast();
    }
}
