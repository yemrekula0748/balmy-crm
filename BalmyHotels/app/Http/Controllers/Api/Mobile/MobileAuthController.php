<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with('branch')->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'E-posta veya sifre hatali.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Hesabiniz devre disi birakilmis.'], 403);
        }

        $user->tokens()->where('name', 'mobile')->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json($this->authPayload($user, $token));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->authPayload($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->where('name', 'mobile')->delete();

        return response()->json(['message' => 'Cikis yapildi.']);
    }

    private function authPayload(User $user, ?string $token = null): array
    {
        $user->loadMissing('branch');

        $roles = $this->roleNames($user);
        $payload = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'branch_id' => $user->branch_id,
                'branch_name' => $user->branch?->name,
            ],
            'is_super_admin' => in_array('super_admin', $roles, true),
            'roles' => $roles,
            'permissions' => $this->permissionsForRoles($roles),
        ];

        if ($token !== null) {
            $payload = ['token' => $token] + $payload;
        }

        return $payload;
    }

    private function roleNames(User $user): array
    {
        $roles = UserRole::where('user_id', $user->id)->pluck('role_name')->toArray();

        if ($user->role) {
            $roles[] = $user->role;
        }

        return array_values(array_unique(array_filter($roles)));
    }

    private function permissionsForRoles(array $roles): array
    {
        $actions = ['index', 'show', 'create', 'edit', 'delete'];
        $permissions = [];

        foreach (array_keys(RolePermission::flatModules()) as $module) {
            foreach ($actions as $action) {
                $permissions[$module][$action] = in_array('super_admin', $roles, true);
            }
        }

        if (in_array('super_admin', $roles, true) || empty($roles)) {
            return $permissions;
        }

        RolePermission::whereIn('role_name', $roles)->get()->each(function (RolePermission $permission) use (&$permissions, $actions) {
            foreach ($actions as $action) {
                $permissions[$permission->module][$action] = (bool) (
                    ($permissions[$permission->module][$action] ?? false) || $permission->{"can_{$action}"}
                );
            }
        });

        return $permissions;
    }
}
