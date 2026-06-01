<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\RolePermission;
use App\Models\UserRole;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const HUMAN_RESOURCES_ROLE_NAMES = [
        'insan_kaynaklari',
        'ik',
        'hr',
        'human_resources',
    ];

    protected $fillable = [
        'name', 'email', 'password',
        'branch_id', 'department_id', 'role',
        'phone', 'avatar', 'title', 'is_active', 'fault_notify',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
        'fault_notify'      => 'boolean',
    ];

    // --- İlişkiler ---
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /** Pivot: kullanıcıya atanmış tüm roller */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Kullanıcının sahip olduğu tüm rol isimlerini döner.
     * (user_roles pivot tablosundan gelir — model üzerinde cache'lenir)
     */
    public function allRoleNames(): array
    {
        $roleNames = $this->relationLoaded('userRoles')
            ? $this->userRoles->pluck('role_name')->toArray()
            : $this->userRoles()->pluck('role_name')->toArray();

        if (!empty($this->role)) {
            $roleNames[] = $this->role;
        }

        return array_values(array_unique(array_filter($roleNames)));
    }

    /** Verilen rollerden en az biri kullanıcıda varsa true */
    public function hasAnyRole(array $roles): bool
    {
        return count(array_intersect($roles, $this->allRoleNames())) > 0;
    }

    /** Egitim modulu icin ogrenene atanabilecek kullanicilar */
    public function scopeLearners($query)
    {
        return $query->where(function ($roleQuery) {
            $roleQuery->where('role', 'ogrenen')
                ->orWhereHas('userRoles', fn ($userRoleQuery) => $userRoleQuery->where('role_name', 'ogrenen'));
        });
    }

    // --- Rol yardımcıları ---
    public function isSuperAdmin(): bool    { return $this->hasAnyRole(['super_admin']); }
    public function isBranchManager(): bool { return $this->hasAnyRole(['branch_manager']); }
    public function isDeptManager(): bool   { return $this->hasAnyRole(['dept_manager']); }
    public function isHumanResources(): bool
    {
        $roleNames = array_map(fn ($roleName) => $this->normaliseRoleName($roleName), $this->allRoleNames());

        return count(array_intersect(self::HUMAN_RESOURCES_ROLE_NAMES, $roleNames)) > 0;
    }

    /**
     * Kullanıcının belirli modül + eylem için yetkisi var mı?
     * super_admin her zaman true döner.
     * Birden fazla rol varsa, herhangi biri izin veriyorsa true.
     * action: index | show | create | edit | delete
     */
    public function hasPermission(string $module, string $action = 'index'): bool
    {
        if ($this->isSuperAdmin()) return true;

        static $cache = [];
        $cacheKey = "{$this->id}:{$module}:{$action}";

        if (!isset($cache[$cacheKey])) {
            $roleNames = $this->allRoleNames();
            $roleNames = array_values(array_unique(array_filter(array_merge(
                $roleNames,
                array_map(fn ($roleName) => $this->normaliseRoleName($roleName), $roleNames)
            ))));

            $cache[$cacheKey] = !empty($roleNames) && RolePermission::whereIn('role_name', $roleNames)
                ->where('module', $module)
                ->where("can_{$action}", true)
                ->exists();
        }

        return $cache[$cacheKey];
    }

    /** Birincil role ait meta verisini döner (display_name, color vb.) */
    public function roleMeta(): ?\App\Models\Role
    {
        return \App\Models\Role::where('name', $this->role)->first();
    }

    /** Kullanıcının görebileceği şube ID'lerini döner */
    public function visibleBranchIds(): array
    {
        if ($this->isSuperAdmin()) {
            return Branch::pluck('id')->toArray();
        }
        return $this->branch_id ? [$this->branch_id] : [];
    }

    /** Servis takipte IK iki oteli birlikte raporlayabilir ve tanimlayabilir. */
    public function visibleShuttleBranchIds(): array
    {
        if ($this->isSuperAdmin() || $this->isHumanResources()) {
            return Branch::where('is_active', true)->pluck('id')->toArray();
        }

        return $this->visibleBranchIds();
    }

    private function normaliseRoleName(?string $roleName): string
    {
        $roleName = mb_strtolower(trim((string) $roleName), 'UTF-8');
        $roleName = strtr($roleName, [
            'ç' => 'c',
            'ğ' => 'g',
            'ı' => 'i',
            'i̇' => 'i',
            'ö' => 'o',
            'ş' => 's',
            'ü' => 'u',
        ]);

        return trim(preg_replace('/[^a-z0-9]+/', '_', $roleName), '_');
    }
}
