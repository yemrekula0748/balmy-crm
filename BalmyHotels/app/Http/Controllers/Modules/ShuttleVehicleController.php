<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\Branch;
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
    }

    public function index(Request $request)
    {
        $user     = Auth::user();
        $branches = Branch::where('is_active', true)->get();
        $branchId = $request->branch_id;

        $vehicles = ShuttleVehicle::with('branch')
            ->whereIn('branch_id', $user->visibleBranchIds())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->search, fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('plate', 'like', "%{$request->search}%"))
            ->orderBy('branch_id')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('modules.shuttle.vehicles.index', compact('vehicles', 'branches', 'branchId'));
    }

    public function create()
    {
        $user     = Auth::user();
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $user->visibleBranchIds())
            ->get();
        $types = ShuttleVehicle::TYPES;
        return view('modules.shuttle.vehicles.create', compact('branches', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:100',
            'plate'     => 'nullable|string|max:20',
            'type'      => 'required|in:minibus,midibus,otobus,diger',
            'capacity'  => 'required|integer|min:1|max:200',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        if ($data['plate']) $data['plate'] = strtoupper(trim($data['plate']));
        ShuttleVehicle::create($data);

        return redirect()->route('shuttle.vehicles.index')
            ->with('success', 'Araç başarıyla eklendi.');
    }

    public function edit(ShuttleVehicle $vehicle)
    {
        $user     = Auth::user();
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $user->visibleBranchIds())
            ->get();
        $types = ShuttleVehicle::TYPES;
        return view('modules.shuttle.vehicles.edit', compact('vehicle', 'branches', 'types'));
    }

    public function update(Request $request, ShuttleVehicle $vehicle)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:100',
            'plate'     => 'nullable|string|max:20',
            'type'      => 'required|in:minibus,midibus,otobus,diger',
            'capacity'  => 'required|integer|min:1|max:200',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        if ($data['plate']) $data['plate'] = strtoupper(trim($data['plate']));
        $vehicle->update($data);

        return redirect()->route('shuttle.vehicles.index')
            ->with('success', 'Araç başarıyla güncellendi.');
    }

    public function destroy(ShuttleVehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('shuttle.vehicles.index')
            ->with('success', 'Araç silindi.');
    }
}
