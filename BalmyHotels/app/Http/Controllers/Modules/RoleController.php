<?php

namespace App\Http\Controllers\Modules;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RoleController extends BaseModuleController
{
    public function __construct()
    {
        // Sadece super_admin erişebilir — middleware bunu handle eder
        // 'roles' modülü için index/create/edit kullanıyoruz
        $this->middleware('perm:roles,index')->only(['index', 'permissions']);
        $this->middleware('perm:roles,create')->only(['store']);
        $this->middleware('perm:roles,edit')->only(['updatePermissions', 'update']);
        $this->middleware('perm:roles,delete')->only(['destroy']);
    }

    /** Roller listesi */
    public function index()
    {
        $roles   = Role::withCount([])->get();
        $modules = RolePermission::MODULES;
        return view('modules.roles.index', compact('roles', 'modules'));
    }

    /** Yeni rol kaydet */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:50|unique:roles,name|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:100',
            'color'        => 'required|string|max:20',
        ]);

        Role::create($data);

        return back()->with('success', 'Rol oluşturuldu.');
    }

    /** Rolün görünen adını / rengini güncelle */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:100',
            'color'        => ['required', 'string', 'max:20', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        $role->update($data);
        return back()->with('success', $role->display_name . ' güncellendi.');
    }

    /** Rolü sil (sistem rolleri silinmez) */
    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'Sistem rolleri silinemez.');
        }
        RolePermission::where('role_name', $role->name)->delete();
        $role->delete();
        return back()->with('success', 'Rol silindi.');
    }

    /** Bir rolün izin matrisini göster */
    public function permissions(Role $role)
    {
        $modules     = RolePermission::MODULES;
        $permissions = RolePermission::where('role_name', $role->name)
                                     ->get()
                                     ->keyBy('module');

        RETURN view('modules.roles.permissions', compact('role', 'modules', 'permissions'));
    }

    /** Bir rolün izinlerini kaydet */
    public function updatePermissions(Request $request, Role $role)
    {
        $flatModules = RolePermission::flatModules();
        $actions     = ['index', 'show', 'create', 'edit', 'delete'];

        foreach (array_keys($flatModules) as $module) {
            $data = [];
            foreach ($actions as $action) {
                $data["can_{$action}"] = (bool) $request->input("perms.{$module}.{$action}");
            }

            RolePermission::updateOrCreate(
                ['role_name' => $role->name, 'module' => $module],
                $data
            );
        }

        return back()->with('success', 'İzinler kaydedildi.');
    }
}
