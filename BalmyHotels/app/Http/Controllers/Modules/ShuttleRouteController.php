<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\Branch;
use App\Models\ShuttleRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShuttleRouteController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'shuttle_routes',
            ['index'],
            [],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }

    public function index(Request $request)
    {
        $user     = Auth::user();
        $branches = Branch::where('is_active', true)->get();
        $branchId = $request->branch_id;

        $routes = ShuttleRoute::with('branch')
            ->whereIn('branch_id', $user->visibleBranchIds())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('branch_id')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('modules.shuttle.routes.index', compact('routes', 'branches', 'branchId'));
    }

    public function create()
    {
        $user     = Auth::user();
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $user->visibleBranchIds())
            ->get();
        return view('modules.shuttle.routes.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id'   => 'required|exists:branches,id',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        ShuttleRoute::create($data);

        return redirect()->route('shuttle.routes.index')
            ->with('success', 'Güzergah başarıyla eklendi.');
    }

    public function edit(ShuttleRoute $route)
    {
        $user     = Auth::user();
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $user->visibleBranchIds())
            ->get();
        return view('modules.shuttle.routes.edit', compact('route', 'branches'));
    }

    public function update(Request $request, ShuttleRoute $route)
    {
        $data = $request->validate([
            'branch_id'   => 'required|exists:branches,id',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $route->update($data);

        return redirect()->route('shuttle.routes.index')
            ->with('success', 'Güzergah başarıyla güncellendi.');
    }

    public function destroy(ShuttleRoute $route)
    {
        $route->delete();
        return redirect()->route('shuttle.routes.index')
            ->with('success', 'Güzergah silindi.');
    }
}
