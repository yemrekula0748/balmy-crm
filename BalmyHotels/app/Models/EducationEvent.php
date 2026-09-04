<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationEvent extends Model
{
    protected $fillable = [
        'trainer_id',
        'title',
        'description',
        'language',
        'location',
        'starts_at',
        'ends_at',
        'response_deadline_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'response_deadline_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EducationEventResponse::class);
    }

    public function getLanguageLabelAttribute(): string
    {
        return EducationCourse::LANGUAGES[$this->language] ?? strtoupper((string) $this->language);
    }

    public function canBeDeletedBy(User $user): bool
    {
        if (! $user->hasPermission('education_events', 'delete')) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->isBranchManager()
            || (int) $this->trainer_id === (int) $user->id;
    }
}
