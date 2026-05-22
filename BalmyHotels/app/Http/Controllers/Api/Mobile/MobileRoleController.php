<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileRoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Role::orderBy('display_name')
                ->get(['name', 'display_name', 'color', 'is_system']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z_]+$/', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:20'],
        ]);

        $role = Role::create($data);

        return response()->json(['data' => $role], 201);
    }

    public function destroy(string $name): JsonResponse
    {
        RolePermission::where('role_name', $name)->delete();
        UserRole::where('role_name', $name)->delete();
        Role::where('name', $name)->delete();

        return response()->json(null, 204);
    }

    public function permissions(Request $request): JsonResponse
    {
        $request->validate([
            'role_name' => ['required', 'string', 'max:50'],
        ]);

        return response()->json([
            'data' => RolePermission::where('role_name', $request->input('role_name'))
                ->orderBy('module')
                ->get(['role_name', 'module', 'can_index', 'can_show', 'can_create', 'can_edit', 'can_delete']),
        ]);
    }

    public function updatePermission(Request $request): JsonResponse
    {
        $data = $request->validate([
            'role_name' => ['required', 'string', 'max:50'],
            'module' => ['required', 'string', 'max:60'],
            'can_index' => ['sometimes', 'boolean'],
            'can_show' => ['sometimes', 'boolean'],
            'can_create' => ['sometimes', 'boolean'],
            'can_edit' => ['sometimes', 'boolean'],
            'can_delete' => ['sometimes', 'boolean'],
        ]);

        $permission = RolePermission::updateOrCreate(
            [
                'role_name' => $data['role_name'],
                'module' => $data['module'],
            ],
            [
                'can_index' => (bool) ($data['can_index'] ?? false),
                'can_show' => (bool) ($data['can_show'] ?? false),
                'can_create' => (bool) ($data['can_create'] ?? false),
                'can_edit' => (bool) ($data['can_edit'] ?? false),
                'can_delete' => (bool) ($data['can_delete'] ?? false),
            ]
        );

        return response()->json(['data' => $permission]);
    }
}
