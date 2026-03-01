<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use Illuminate\Http\Request;

class VehicleController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'vehicles',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }


    /** Araç listesi */
    public function index(Request $request)
    {
        $branches   = Branch::where('is_active', true)->get();
        $branchId   = $request->branch_id;

        $vehicles = Vehicle::with(['branch', 'lastMaintenance', 'activeInsurance', 'activeCasco'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->search, fn ($q) => $q
                ->where('plate', 'like', "%{$request->search}%")
                ->orWhere('brand', 'like', "%{$request->search}%")
                ->orWhere('model', 'like', "%{$request->search}%"))
            ->orderBy('plate')
            ->paginate(15)
            ->withQueryString();

        // 30 gün içinde süresi dolacak sigorta/kasko uyarıları
        $warnings = VehicleInsurance::with('vehicle')
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays(30))
            ->orderBy('end_date')
            ->get();

        return view('modules.vehicles.index', compact('vehicles', 'branches', 'branchId', 'warnings'));
    }

    /** Araç ekleme formu */
    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('modules.vehicles.create', compact('branches'));
    }

    /** Araç kaydet */
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'plate'          => 'required|string|max:20|unique:vehicles,plate',
            'brand'          => 'required|string|max:60',
            'model'          => 'required|string|max:60',
            'year'           => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'color'          => 'nullable|string|max:30',
            'type'           => 'required|in:binek,minibus,kamyonet,kamyon,diger',
            'current_km'     => 'required|integer|min:0',
            'license_expiry' => 'nullable|date',
            'chassis_no'     => 'nullable|string|max:50',
            'engine_no'      => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ]);

        $data['plate'] = strtoupper(trim($data['plate']));
        Vehicle::create($data);

        return redirect()->route('vehicles.index')
            ->with('success', 'Araç başarıyla eklendi.');
    }

    /** Araç detay */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'branch',
            'operations.user',
            'maintenances.user',
            'insurances',
        ]);

        return view('modules.vehicles.show', compact('vehicle'));
    }

    /** Araç düzenleme formu */
    public function edit(Vehicle $vehicle)
    {
        $branches = Branch::where('is_active', true)->get();
        return view('modules.vehicles.edit', compact('vehicle', 'branches'));
    }

    /** Araç güncelle */
    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'plate'          => 'required|string|max:20|unique:vehicles,plate,' . $vehicle->id,
            'brand'          => 'required|string|max:60',
            'model'          => 'required|string|max:60',
            'year'           => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'color'          => 'nullable|string|max:30',
            'type'           => 'required|in:binek,minibus,kamyonet,kamyon,diger',
            'current_km'     => 'required|integer|min:0',
            'license_expiry' => 'nullable|date',
            'chassis_no'     => 'nullable|string|max:50',
            'engine_no'      => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ]);

        $data['plate']     = strtoupper(trim($data['plate']));
        $data['is_active'] = $request->boolean('is_active');
        $vehicle->update($data);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Araç güncellendi.');
    }

    /** Araç sil */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')
            ->with('success', 'Araç silindi.');
    }

}
