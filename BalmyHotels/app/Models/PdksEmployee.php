<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PdksEmployee extends Model
{
    public const SOURCE_ELEKTRA_FORESTA = 'elektra_foresta';

    protected $fillable = [
        'branch_id', 'department_id', 'user_id', 'source', 'tenant_id', 'company_id',
        'external_employee_id', 'registry_no', 'pdks_card_no', 'identity_no',
        'name', 'email', 'phone', 'title', 'employment_type', 'started_at',
        'ended_at', 'is_active', 'raw_payload', 'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'raw_payload' => 'array',
        'started_at' => 'date',
        'ended_at' => 'date',
        'last_synced_at' => 'datetime',
    ];

    public function branch() { return $this->belongsTo(Branch::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function attendanceRecords() { return $this->hasMany(PdksAttendanceRecord::class, 'employee_id'); }
    public function shiftAssignments() { return $this->hasMany(PdksShiftAssignment::class, 'employee_id'); }
    public function leaveRequests() { return $this->hasMany(PdksLeaveRequest::class, 'employee_id'); }
    public function overtimeRequests() { return $this->hasMany(PdksOvertimeRequest::class, 'employee_id'); }

    public function nationality(): ?string
    {
        $nationality = $this->rawPayloadValue('UYRUK');

        return $nationality !== null ? Str::squish($nationality) : null;
    }

    public function personnelOrigin(): string
    {
        $payrollType = $this->normalisePersonnelValue(
            $this->employment_type ?: $this->rawPayloadValue('BORDROTIPI')
        );
        $nationality = $this->normalisePersonnelValue($this->nationality());
        $title = $this->normalisePersonnelValue($this->title);

        if (Str::contains($payrollType, ['YABANCI', 'FOREIGN'])
            || Str::contains($title, ['YABANCI', 'FOREIGN'])) {
            return 'foreign';
        }

        if ($nationality !== '') {
            return Str::contains($nationality, ['TURKIYE', 'TURKEY'])
                ? 'domestic'
                : 'foreign';
        }

        return 'unknown';
    }

    private function rawPayloadValue(string $wantedKey): ?string
    {
        foreach ((array) ($this->raw_payload ?? []) as $key => $value) {
            if (Str::upper((string) $key) !== Str::upper($wantedKey)
                || (! is_scalar($value) && $value !== null)) {
                continue;
            }

            $value = trim((string) $value);

            return $value !== '' ? $value : null;
        }

        return null;
    }

    private function normalisePersonnelValue(?string $value): string
    {
        return Str::upper(Str::ascii(Str::squish((string) $value)));
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin() || $user->isHumanResources()) {
            return $query;
        }

        if ($user->isDeptManager() && $user->department_id) {
            return $query->where('department_id', $user->department_id);
        }

        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('branch_id', $user->branch_id);
        });
    }
}
