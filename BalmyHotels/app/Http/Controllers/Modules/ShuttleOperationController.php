<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\Branch;
use App\Models\ShuttleTrip;
use App\Models\ShuttleVehicle;
use App\Models\ShuttleRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShuttleOperationController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'shuttle_operations',
            ['index'],
            [],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }

    public function index(Request $request)
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();
        $branches  = Branch::where('is_active', true)->whereIn('id', $branchIds)->get();

        $date     = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $branchId = $request->branch_id ?? ($branches->count() === 1 ? $branches->first()->id : null);

        // Araçları seçili şubeye göre filtrele
        $vehicles = ShuttleVehicle::where('is_active', true)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('branch_id', $branchIds)
            ->orderBy('name')
            ->get();

        $routes = ShuttleRoute::where('is_active', true)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('branch_id', $branchIds)
            ->orderBy('name')
            ->get();

        // Bugünün seferleri
        $trips = ShuttleTrip::with(['vehicle', 'route', 'creator'])
            ->whereIn('branch_id', $branchIds)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('trip_date', $date->toDateString())
            ->orderBy('shift')
            ->orderBy('arrival_time')
            ->get();

        // Günlük özet
        $totalArrival   = $trips->sum('arrival_count');
        $totalDeparture = $trips->sum('departure_count');
        $totalTrips     = $trips->count();

        return view('modules.shuttle.operations.index', compact(
            'trips', 'vehicles', 'routes', 'branches', 'branchId', 'date',
            'totalArrival', 'totalDeparture', 'totalTrips'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'shuttle_vehicle_id' => 'required|exists:shuttle_vehicles,id',
            'route_id'           => 'nullable|exists:shuttle_routes,id',
            'branch_id'          => 'required|exists:branches,id',
            'shift'              => 'required|in:A Shifti,B Shifti,C Shifti,Ara Shift 12-9,İdari,Lojman',
            'trip_date'          => 'required|date',
            'arrival_time'       => 'nullable|date_format:H:i',
            'arrival_count'      => 'required|integer|min:0|max:500',
            'departure_time'     => 'nullable|date_format:H:i',
            'departure_count'    => 'required|integer|min:0|max:500',
            'notes'              => 'nullable|string|max:500',
        ]);

        $data['created_by'] = $user->id;
        ShuttleTrip::create($data);

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $data['branch_id'],
            'date'      => $data['trip_date'],
        ])->with('success', 'Sefer kaydedildi.');
    }

    public function edit(ShuttleTrip $operation)
    {
        $user      = Auth::user();
        $branchIds = $user->visibleBranchIds();
        $vehicles  = ShuttleVehicle::where('is_active', true)
            ->whereIn('branch_id', $branchIds)->orderBy('name')->get();
        $routes = ShuttleRoute::where('is_active', true)
            ->whereIn('branch_id', $branchIds)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->whereIn('id', $branchIds)->get();
        $shifts   = ShuttleTrip::SHIFTS;

        return view('modules.shuttle.operations.edit', compact(
            'operation', 'vehicles', 'routes', 'branches', 'shifts'
        ));
    }

    public function update(Request $request, ShuttleTrip $operation)
    {
        $data = $request->validate([
            'shuttle_vehicle_id' => 'required|exists:shuttle_vehicles,id',
            'route_id'           => 'nullable|exists:shuttle_routes,id',
            'branch_id'          => 'required|exists:branches,id',
            'shift'              => 'required|in:A Shifti,B Shifti,C Shifti,Ara Shift 12-9,İdari,Lojman',
            'trip_date'          => 'required|date',
            'arrival_time'       => 'nullable|date_format:H:i',
            'arrival_count'      => 'required|integer|min:0|max:500',
            'departure_time'     => 'nullable|date_format:H:i',
            'departure_count'    => 'required|integer|min:0|max:500',
            'notes'              => 'nullable|string|max:500',
        ]);

        $operation->update($data);

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $data['branch_id'],
            'date'      => $data['trip_date'],
        ])->with('success', 'Sefer güncellendi.');
    }

    public function departure(Request $request, ShuttleTrip $operation)
    {
        $data = $request->validate([
            'departure_time'  => 'nullable|date_format:H:i',
            'departure_count' => 'required|integer|min:0|max:500',
        ]);

        $operation->update($data);

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $operation->branch_id,
            'date'      => $operation->trip_date->toDateString(),
        ])->with('success', 'Dönüş bilgisi kaydedildi.');
    }

    public function destroy(ShuttleTrip $operation)
    {
        $branchId = $operation->branch_id;
        $date     = $operation->trip_date->toDateString();
        $operation->delete();

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $branchId,
            'date'      => $date,
        ])->with('success', 'Sefer silindi.');
    }
}
