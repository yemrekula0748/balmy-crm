<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleOperationController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'vehicle_ops',
            [],
            [],
            ['store'],
            ['update'],
            ['destroy']
        );
    }


    /** Araç operasyonlarını listele (araç detay sayfasında inline) */
    public function index(Vehicle $vehicle)
    {
        $operations = $vehicle->operations()->with('user')->latest()->paginate(20);
        return view('modules.vehicles.operations.index', compact('vehicle', 'operations'));
    }

    /** Operasyon kaydet */
    public function store(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'type'         => 'required|in:giris,cikis,goreve_gidis,gorevden_gelis',
            'operation_at' => 'required|date',
            'km'           => 'required|integer|min:0',
            'destination'  => 'nullable|string|max:120',
            'notes'        => 'nullable|string',
        ]);

        if ($data['km'] < $vehicle->current_km) {
            return back()->withErrors(['km' => 'Girilen km mevcut araç km değerinden küçük olamaz.'])->withInput();
        }

        $data['vehicle_id'] = $vehicle->id;
        $data['user_id']    = Auth::id();

        VehicleOperation::create($data);

        // Km güncelle
        $vehicle->update(['current_km' => $data['km']]);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Operasyon kaydedildi.');
    }

    /** Operasyon sil */
    public function destroy(Vehicle $vehicle, VehicleOperation $operation)
    {
        abort_unless($operation->vehicle_id === $vehicle->id, 404);
        $operation->delete();
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Operasyon silindi.');
    }

    public function create(Vehicle $vehicle) { return redirect()->route('vehicles.show', $vehicle); }
    public function show(Vehicle $vehicle, string $id) { return redirect()->route('vehicles.show', $vehicle); }
    public function edit(Vehicle $vehicle, string $id) { return redirect()->route('vehicles.show', $vehicle); }
    public function update(Request $request, Vehicle $vehicle, string $id) { return redirect()->route('vehicles.show', $vehicle); }
}
