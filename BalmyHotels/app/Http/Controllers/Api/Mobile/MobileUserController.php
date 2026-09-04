<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MobileUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['branch', 'department'])->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        return response()->json($this->paginated($users, fn (User $user) => $this->userPayload($user)));
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with(['branch', 'department'])->findOrFail($id);

        return response()->json(['data' => $this->userPayload($user)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:50'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['phone_normalized'] = User::normalizeTurkishPhone($data['phone'] ?? null);
        $user = User::create($data);
        $this->syncPrimaryRole($user, $data['role'] ?? null);

        return response()->json(['data' => $this->userPayload($user->load(['branch', 'department']))], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'title' => ['sometimes', 'nullable', 'string', 'max:100'],
            'role' => ['sometimes', 'nullable', 'string', 'max:50'],
            'branch_id' => ['sometimes', 'nullable', 'exists:branches,id'],
            'department_id' => ['sometimes', 'nullable', 'exists:departments,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('password', $data)) {
            if ($data['password']) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
        }

        if (array_key_exists('phone', $data)) {
            $data['phone_normalized'] = User::normalizeTurkishPhone($data['phone']);
        }

        $user->update($data);

        if (array_key_exists('role', $data)) {
            $this->syncPrimaryRole($user, $data['role']);
        }

        return response()->json(['data' => $this->userPayload($user->load(['branch', 'department']))]);
    }

    public function destroy(int $id): JsonResponse
    {
        User::findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'title' => $user->title,
            'role' => $user->role,
            'branch_id' => $user->branch_id,
            'branch' => $user->branch ? ['name' => $user->branch->name] : null,
            'department_id' => $user->department_id,
            'department' => $user->department ? ['name' => $user->department->name] : null,
            'profile_photo' => $user->avatar ? asset('storage/' . ltrim($user->avatar, '/')) : null,
            'is_active' => (bool) $user->is_active,
            'is_elektra_user' => $user->elektra_sicil_id !== null,
            'created_at' => optional($user->created_at)->toISOString(),
        ];
    }

    private function syncPrimaryRole(User $user, ?string $role): void
    {
        if (! $role) {
            return;
        }

        UserRole::updateOrCreate([
            'user_id' => $user->id,
            'role_name' => $role,
        ]);
    }

    private function paginated($paginator, callable $map): array
    {
        return [
            'data' => $paginator->getCollection()->map($map)->values(),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
