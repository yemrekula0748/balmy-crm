<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\ShuttleRoute;
use App\Models\ShuttleVehicle;
use Illuminate\Http\Request;

class ShuttleVehicleController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'shuttle_vehicles',
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
        $vehicles = ShuttleVehicle::with('routes')
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('plate', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('modules.shuttle.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $routes = ShuttleRoute::active()
            ->orderBy('name')
            ->get();
        $types = ShuttleVehicle::TYPES;

        return view('modules.shuttle.vehicles.create', compact('types', 'routes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'plate' => 'nullable|string|max:20',
            'type' => 'required|in:minibus,midibus,otobus,diger',
            'capacity' => 'required|integer|min:1|max:200',
            'is_active' => 'boolean',
            'route_ids' => 'nullable|array',
            'route_ids.*' => 'integer|exists:shuttle_routes,id',
        ]);

        $data['branch_id'] = null;
        $data['is_active'] = $request->boolean('is_active', true);
        if (! empty($data['plate'])) {
            $data['plate'] = strtoupper(trim($data['plate']));
        }

        $routeIds = collect($request->input('route_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();
        $allowedRouteIds = ShuttleRoute::query()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        if ($routeIds->diff($allowedRouteIds)->isNotEmpty()) {
            abort(403);
        }

        $vehicle = ShuttleVehicle::create(collect($data)->except('route_ids')->all());
        $vehicle->routes()->sync($routeIds);

        return redirect()->route('shuttle.vehicles.index')
            ->with('success', 'Arac basariyla eklendi.');
    }

    public function edit(ShuttleVehicle $vehicle)
    {
        $routes = ShuttleRoute::active()
            ->orderBy('name')
            ->get();
        $types = ShuttleVehicle::TYPES;

        $vehicle->load('routes');

        return view('modules.shuttle.vehicles.edit', compact('vehicle', 'types', 'routes'));
    }

    public function update(Request $request, ShuttleVehicle $vehicle)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'plate' => 'nullable|string|max:20',
            'type' => 'required|in:minibus,midibus,otobus,diger',
            'capacity' => 'required|integer|min:1|max:200',
            'is_active' => 'boolean',
            'route_ids' => 'nullable|array',
            'route_ids.*' => 'integer|exists:shuttle_routes,id',
        ]);

        $data['branch_id'] = null;
        $data['is_active'] = $request->boolean('is_active', true);
        if (! empty($data['plate'])) {
            $data['plate'] = strtoupper(trim($data['plate']));
        }

        $routeIds = collect($request->input('route_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();
        $allowedRouteIds = ShuttleRoute::query()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        if ($routeIds->diff($allowedRouteIds)->isNotEmpty()) {
            abort(403);
        }

        $vehicle->update(collect($data)->except('route_ids')->all());
        $vehicle->routes()->sync($routeIds);

        return redirect()->route('shuttle.vehicles.index')
            ->with('success', 'Arac basariyla guncellendi.');
    }

    public function destroy(ShuttleVehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('shuttle.vehicles.index')
            ->with('success', 'Arac silindi.');
    }
}
