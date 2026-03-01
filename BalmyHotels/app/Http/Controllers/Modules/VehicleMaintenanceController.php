<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleMaintenanceController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'vehicle_maint',
            [],
            [],
            ['store'],
            [],
            ['destroy']
        );
    }


    /** Bakım kaydet */
    public function store(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'type'            => 'required|in:servis,lastik,yag,egzoz,fren,diger',
            'maintenance_at'  => 'required|date',
            'km'              => 'required|integer|min:0',
            'cost'            => 'nullable|numeric|min:0',
            'service_name'    => 'nullable|string|max:100',
            'next_km'         => 'nullable|integer|min:0',
            'next_date'       => 'nullable|date',
            'notes'           => 'nullable|string',
        ]);

        $data['vehicle_id'] = $vehicle->id;
        $data['user_id']    = Auth::id();

        VehicleMaintenance::create($data);

        // km güncelle (bakım kaydedilen km mevcut km'den büyükse)
        if ($data['km'] > $vehicle->current_km) {
            $vehicle->update(['current_km' => $data['km']]);
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Bakım kaydedildi.');
    }

    /** Bakım sil */
    public function destroy(Vehicle $vehicle, VehicleMaintenance $maintenance)
    {
        abort_unless($maintenance->vehicle_id === $vehicle->id, 404);
        $maintenance->delete();
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Bakım kaydı silindi.');
    }

    public function index(Vehicle $vehicle) { return redirect()->route('vehicles.show', $vehicle); }
    public function create(Vehicle $vehicle) { return redirect()->route('vehicles.show', $vehicle); }
    public function show(Vehicle $vehicle, string $id) { return redirect()->route('vehicles.show', $vehicle); }
    public function edit(Vehicle $vehicle, string $id) { return redirect()->route('vehicles.show', $vehicle); }
    public function update(Request $request, Vehicle $vehicle, string $id) { return redirect()->route('vehicles.show', $vehicle); }
}
