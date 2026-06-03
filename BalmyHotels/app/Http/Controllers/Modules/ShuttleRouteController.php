<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\ShuttleRoute;
use Illuminate\Http\Request;

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

        $this->middleware(function ($request, $next) {
            $user = $request->user();
            abort_unless($user && ($user->isSuperAdmin() || $user->isHumanResources()), 403);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $routes = ShuttleRoute::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('modules.shuttle.routes.index', compact('routes'));
    }

    public function create()
    {
        return view('modules.shuttle.routes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['branch_id'] = null;
        $data['is_active'] = $request->boolean('is_active', true);

        ShuttleRoute::create($data);

        return redirect()->route('shuttle.routes.index')
            ->with('success', 'Guzergah basariyla eklendi.');
    }

    public function edit(ShuttleRoute $route)
    {
        return view('modules.shuttle.routes.edit', compact('route'));
    }

    public function update(Request $request, ShuttleRoute $route)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['branch_id'] = null;
        $data['is_active'] = $request->boolean('is_active', true);

        $route->update($data);

        return redirect()->route('shuttle.routes.index')
            ->with('success', 'Guzergah basariyla guncellendi.');
    }

    public function destroy(ShuttleRoute $route)
    {
        $route->delete();

        return redirect()->route('shuttle.routes.index')
            ->with('success', 'Guzergah silindi.');
    }
}
