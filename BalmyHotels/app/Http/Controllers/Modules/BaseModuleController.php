<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;

/**
 * Tüm modül controller'larının temel sınıfı.
 * requirePermission() ile tek satırda CRUD izin middleware'i tanımlanır.
 */
abstract class BaseModuleController extends Controller
{
    /**
     * Modül için standart CRUD izinlerini atar.
     *
     * @param  string  $module    RolePermission::MODULES'daki key
     * @param  array   $index     index iznini kullanan metodlar
     * @param  array   $show      show iznini kullanan metodlar
     * @param  array   $create    create iznini kullanan metodlar
     * @param  array   $edit      edit iznini kullanan metodlar
     * @param  array   $delete    delete iznini kullanan metodlar
     */
    protected function requirePermission(
        string $module,
        array  $index  = ['index'],
        array  $show   = ['show'],
        array  $create = ['create', 'store'],
        array  $edit   = ['edit', 'update'],
        array  $delete = ['destroy']
    ): void {
        if ($index)  $this->middleware("perm:{$module},index")->only($index);
        if ($show)   $this->middleware("perm:{$module},show")->only($show);
        if ($create) $this->middleware("perm:{$module},create")->only($create);
        if ($edit)   $this->middleware("perm:{$module},edit")->only($edit);
        if ($delete) $this->middleware("perm:{$module},delete")->only($delete);
    }
}
