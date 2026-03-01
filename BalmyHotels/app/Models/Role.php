<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'display_name', 'color', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    /** Tüm modüller ve grupları — RolePermission ile senkron olmalı */
    public static function availableModules(): array
    {
        return RolePermission::MODULES;
    }

    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role_name', 'name');
    }

    /** Belirli modül için permission kaydını döner (yoksa null) */
    public function permissionFor(string $module): ?RolePermission
    {
        return $this->permissions()->where('module', $module)->first();
    }
}
