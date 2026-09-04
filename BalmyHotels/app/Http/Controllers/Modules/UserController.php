<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\PdksEmployee;
use App\Models\User;
use App\Models\Role;
use App\Services\ElektraUserSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Throwable;

class UserController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'users',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update', 'syncElektra'],
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
        $status = (string) $request->get('status', '');
        if (! in_array($status, ['', 'active', 'inactive', 'elektra_inactive'], true)) {
            $status = '';
        }

        $query = User::with([
            'branch',
            'department',
            'userRoles',
            'elektraPdksEmployee:id,user_id,external_employee_id,is_active,ended_at,last_synced_at',
        ])->orderBy('name');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('role')) {
            $query->where(function ($roleQuery) use ($request) {
                $roleQuery->where('role', $request->role)
                    ->orWhereHas('userRoles', fn ($userRoleQuery) => $userRoleQuery->where('role_name', $request->role));
            });
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'elektra_inactive') {
            $query->where('is_active', false)
                ->whereHas('elektraPdksEmployee', fn ($employeeQuery) => $employeeQuery->where('is_active', false));
        }

        $users    = $query->paginate(20)->withQueryString();
        $branches = Branch::orderBy('name')->get();
        $roles    = $this->getRoles();
        $page_title = 'Çalışanlar';

        $elektraVerifiedInactiveQuery = User::query()
            ->where('is_active', false)
            ->whereHas('elektraPdksEmployee', fn ($employeeQuery) => $employeeQuery->where('is_active', false));

        $userStats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'elektra_inactive' => (clone $elektraVerifiedInactiveQuery)->count(),
            'branches' => Branch::count(),
        ];

        $elektraLastVerifiedAt = PdksEmployee::query()
            ->where('source', PdksEmployee::SOURCE_ELEKTRA_FORESTA)
            ->max('last_synced_at');
        $elektraLastVerifiedAt = $elektraLastVerifiedAt
            ? Carbon::parse($elektraLastVerifiedAt)
            : null;

        return view('modules.users.index', compact(
            'users',
            'branches',
            'roles',
            'page_title',
            'status',
            'userStats',
            'elektraLastVerifiedAt'
        ));
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
            'phone_normalized' => User::normalizeTurkishPhone($request->phone),
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
        $data['phone_normalized'] = User::normalizeTurkishPhone($request->phone);
        $data['role']        = $primaryRole;
        $data['is_active']   = $request->boolean('is_active');
        $data['fault_notify'] = $request->boolean('fault_notify');

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

    public function syncElektra(ElektraUserSyncService $syncService)
    {
        try {
            $result = $syncService->syncForesta();
        } catch (Throwable $exception) {
            report($exception);

            $reason = $exception instanceof RuntimeException
                ? $exception->getMessage()
                : 'Beklenmeyen bir bağlantı veya veritabanı hatası oluştu.';

            return redirect()->route('users.index')
                ->with('error', 'Elektra senkronizasyonu tamamlanamadı: ' . $reason);
        }

        $message = sprintf(
            'Elektra senkronizasyonu tamamlandı: %d aktif sicil, %d yeni üye, %d güncellenen, %d eşleştirilen mevcut hesap, %d yeniden aktif, %d pasife alınan.',
            $result['total'],
            $result['created'],
            $result['updated'],
            $result['adopted'],
            $result['reactivated'],
            $result['deactivated']
        );

        $redirect = redirect()->route('users.index')->with('success', $message);

        if ($result['conflicts'] > 0 || $result['login_unavailable'] > 0) {
            $warning = sprintf(
                '%d kayıt güvenli eşleşme yapılamadığı için atlandı; %d üyede TC veya geçerli telefon eksik olduğundan personel girişi kullanılamıyor.',
                $result['conflicts'],
                $result['login_unavailable']
            );

            if ($result['conflict_names'] !== []) {
                $warning .= ' Kontrol edilmesi gerekenler: ' . implode(', ', $result['conflict_names']) . '.';
            }

            $redirect->with('warning', $warning);
        }

        return $redirect;
    }
}
