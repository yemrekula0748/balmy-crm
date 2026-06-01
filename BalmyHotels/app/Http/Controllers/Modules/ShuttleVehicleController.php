<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\Branch;
use App\Models\ShuttleRoute;
use App\Models\ShuttleVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        })->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $visibleBranchIds = array_map('intval', $user->visibleShuttleBranchIds());
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $visibleBranchIds)
            ->orderBy('name')
            ->get();
        $branchId = $request->filled('branch_id') && in_array((int) $request->branch_id, $visibleBranchIds, true)
            ? (int) $request->branch_id
            : null;

        $vehicles = ShuttleVehicle::with(['branch', 'routes'])
            ->whereIn('branch_id', $visibleBranchIds)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('plate', 'like', "%{$search}%");
                });
            })
            ->orderBy('branch_id')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('modules.shuttle.vehicles.index', compact('vehicles', 'branches', 'branchId'));
    }

    public function create()
    {
        $user = Auth::user();
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $user->visibleShuttleBranchIds())
            ->get();
        $routes = ShuttleRoute::with('branch')
            ->active()
            ->whereIn('branch_id', $user->visibleShuttleBranchIds())
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();
        $types = ShuttleVehicle::TYPES;

        return view('modules.shuttle.vehicles.create', compact('branches', 'types', 'routes'));
    }

    public function store(Request $request)
    {
        $visibleBranchIds = Auth::user()->visibleShuttleBranchIds();
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:100',
            'plate' => 'nullable|string|max:20',
            'type' => 'required|in:minibus,midibus,otobus,diger',
            'capacity' => 'required|integer|min:1|max:200',
            'is_active' => 'boolean',
            'route_ids' => 'nullable|array',
            'route_ids.*' => 'integer|exists:shuttle_routes,id',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        if (! in_array((int) $data['branch_id'], array_map('intval', $visibleBranchIds), true)) {
            abort(403);
        }
        if (! empty($data['plate'])) {
            $data['plate'] = strtoupper(trim($data['plate']));
        }

        $routeIds = collect($request->input('route_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();
        $allowedRouteIds = ShuttleRoute::whereIn('branch_id', $visibleBranchIds)
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
        $user = Auth::user();
        abort_unless(in_array((int) $vehicle->branch_id, array_map('intval', $user->visibleShuttleBranchIds()), true), 403);
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $user->visibleShuttleBranchIds())
            ->get();
        $routes = ShuttleRoute::with('branch')
            ->active()
            ->whereIn('branch_id', $user->visibleShuttleBranchIds())
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();
        $types = ShuttleVehicle::TYPES;

        $vehicle->load('routes');

        return view('modules.shuttle.vehicles.edit', compact('vehicle', 'branches', 'types', 'routes'));
    }

    public function update(Request $request, ShuttleVehicle $vehicle)
    {
        $visibleBranchIds = Auth::user()->visibleShuttleBranchIds();
        abort_unless(in_array((int) $vehicle->branch_id, array_map('intval', $visibleBranchIds), true), 403);
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:100',
            'plate' => 'nullable|string|max:20',
            'type' => 'required|in:minibus,midibus,otobus,diger',
            'capacity' => 'required|integer|min:1|max:200',
            'is_active' => 'boolean',
            'route_ids' => 'nullable|array',
            'route_ids.*' => 'integer|exists:shuttle_routes,id',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        if (! in_array((int) $data['branch_id'], array_map('intval', $visibleBranchIds), true)) {
            abort(403);
        }
        if (! empty($data['plate'])) {
            $data['plate'] = strtoupper(trim($data['plate']));
        }

        $routeIds = collect($request->input('route_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();
        $allowedRouteIds = ShuttleRoute::whereIn('branch_id', $visibleBranchIds)
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
        abort_unless(in_array((int) $vehicle->branch_id, array_map('intval', Auth::user()->visibleShuttleBranchIds()), true), 403);
        $vehicle->delete();

        return redirect()->route('shuttle.vehicles.index')
            ->with('success', 'Arac silindi.');
    }
}
