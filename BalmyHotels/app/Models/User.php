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

    protected $fillable = [
        'name', 'email', 'password',
        'branch_id', 'department_id', 'role',
        'phone', 'avatar', 'title', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
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
        return $this->userRoles->pluck('role_name')->toArray();
    }

    /** Verilen rollerden en az biri kullanıcıda varsa true */
    public function hasAnyRole(array $roles): bool
    {
        return count(array_intersect($roles, $this->allRoleNames())) > 0;
    }

    // --- Rol yardımcıları ---
    public function isSuperAdmin(): bool    { return $this->hasAnyRole(['super_admin']); }
    public function isBranchManager(): bool { return $this->hasAnyRole(['branch_manager']); }
    public function isDeptManager(): bool   { return $this->hasAnyRole(['dept_manager']); }

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
}
