<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuShowcaseItem extends Model
{
    protected $fillable = [
        'showcase_id', 'qr_menu_id', 'survey_id', 'label', 'sort_order',
    ];

    public function showcase(): BelongsTo
    {
        return $this->belongsTo(MenuShowcase::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(QrMenu::class, 'qr_menu_id');
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    public function isSurvey(): bool
    {
        return $this->survey_id !== null;
    }
}
