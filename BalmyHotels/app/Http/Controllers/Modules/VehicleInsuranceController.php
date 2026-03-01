<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use Illuminate\Http\Request;

class VehicleInsuranceController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'vehicle_ins',
            [],
            [],
            ['store'],
            ['update'],
            ['destroy']
        );
    }


    /** Sigorta/kasko kaydet */
    public function store(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'type'         => 'required|in:trafik,kasko',
            'company'      => 'required|string|max:100',
            'policy_no'    => 'required|string|max:60',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after:start_date',
            'cost'         => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
        ]);

        $data['vehicle_id'] = $vehicle->id;
        VehicleInsurance::create($data);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Sigorta/Kasko kaydedildi.');
    }

    /** Sigorta düzenle */
    public function update(Request $request, Vehicle $vehicle, VehicleInsurance $insurance)
    {
        abort_unless($insurance->vehicle_id === $vehicle->id, 404);

        $data = $request->validate([
            'type'         => 'required|in:trafik,kasko',
            'company'      => 'required|string|max:100',
            'policy_no'    => 'required|string|max:60',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after:start_date',
            'cost'         => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
        ]);

        $insurance->update($data);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Sigorta güncellendi.');
    }

    /** Sigorta sil */
    public function destroy(Vehicle $vehicle, VehicleInsurance $insurance)
    {
        abort_unless($insurance->vehicle_id === $vehicle->id, 404);
        $insurance->delete();
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Sigorta kaydı silindi.');
    }

    public function index(Vehicle $vehicle) { return redirect()->route('vehicles.show', $vehicle); }
    public function create(Vehicle $vehicle) { return redirect()->route('vehicles.show', $vehicle); }
    public function show(Vehicle $vehicle, string $id) { return redirect()->route('vehicles.show', $vehicle); }
    public function edit(Vehicle $vehicle, string $id) { return redirect()->route('vehicles.show', $vehicle); }
}
