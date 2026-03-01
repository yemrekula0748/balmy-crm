<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'users',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }


    const ROLES = [
        'super_admin'    => 'Süper Admin',
        'branch_manager' => 'Şube Müdürü',
        'dept_manager'   => 'Departman Müdürü',
        'staff'          => 'Personel',
    ];

    /** DB'den tüm rolleri çeker (dinamik) */
    private function getRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::orderBy('display_name')->get();
    }

    public function index(Request $request)
    {
        $query = User::with(['branch', 'department', 'userRoles'])->orderBy('name');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users    = $query->paginate(20)->withQueryString();
        $branches = Branch::orderBy('name')->get();
        $roles    = $this->getRoles();
        $page_title = 'Çalışanlar';

        return view('modules.users.index', compact('users', 'branches', 'roles', 'page_title'));
    }

    public function create()
    {
        $branches   = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $roles      = $this->getRoles();
        $page_title = 'Yeni Çalışan';

        return view('modules.users.create', compact('branches', 'departments', 'roles', 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:6|confirmed',
            'roles'           => 'required|array|min:1',
            'roles.*'         => 'exists:roles,name',
            'branch_id'       => 'nullable|exists:branches,id',
            'department_id'   => 'nullable|exists:departments,id',
            'phone'           => 'nullable|string|max:20',
            'title'           => 'nullable|string|max:100',
        ]);

        // Birincil rol: super_admin varsa o, yoksa ilk seçilen
        $selectedRoles  = $request->roles;
        $primaryRole    = in_array('super_admin', $selectedRoles) ? 'super_admin' : $selectedRoles[0];

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role'          => $primaryRole,
            'branch_id'     => $request->branch_id,
            'department_id' => $request->department_id,
            'phone'         => $request->phone,
            'title'         => $request->title,
            'is_active'     => true,
        ]);

        // Tüm rolleri pivot tabloya kaydet
        foreach ($selectedRoles as $roleName) {
            $user->userRoles()->create(['role_name' => $roleName]);
        }

        return redirect()->route('users.index')->with('success', 'Çalışan başarıyla oluşturuldu.');
    }

    public function show(User $user)
    {
        return redirect()->route('users.edit', $user);
    }

    public function edit(User $user)
    {
        $branches       = Branch::orderBy('name')->get();
        $departments    = Department::orderBy('name')->get();
        $roles          = $this->getRoles();
        $userRoleNames  = $user->userRoles->pluck('role_name')->toArray();
        $page_title     = 'Çalışan Düzenle';

        return view('modules.users.edit', compact('user', 'branches', 'departments', 'roles', 'userRoleNames', 'page_title'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'password'      => 'nullable|string|min:6|confirmed',
            'roles'         => 'required|array|min:1',
            'roles.*'       => 'exists:roles,name',
            'branch_id'     => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'phone'         => 'nullable|string|max:20',
            'title'         => 'nullable|string|max:100',
        ]);

        $selectedRoles = $request->roles;
        $primaryRole   = in_array('super_admin', $selectedRoles) ? 'super_admin' : $selectedRoles[0];

        $data = $request->only(['name', 'email', 'branch_id', 'department_id', 'phone', 'title']);
        $data['role']      = $primaryRole;
        $data['is_active'] = $request->boolean('is_active');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Rolleri pivot tabloya senkronize et
        $user->userRoles()->delete();
        foreach ($selectedRoles as $roleName) {
            $user->userRoles()->create(['role_name' => $roleName]);
        }

        return redirect()->route('users.index')->with('success', 'Çalışan başarıyla güncellendi.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Çalışan silindi.');
    }
}
