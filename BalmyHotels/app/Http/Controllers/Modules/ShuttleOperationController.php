<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\Branch;
use App\Models\ShuttleTrip;
use App\Models\ShuttleVehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $user = Auth::user();
        $branchIds = $user->visibleBranchIds();
        $branches = Branch::where('is_active', true)->whereIn('id', $branchIds)->get();

        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $branchId = $request->branch_id ?? ($branches->count() === 1 ? $branches->first()->id : null);

        $vehicles = ShuttleVehicle::with(['branch', 'routes'])
            ->where('is_active', true)
            ->whereIn('branch_id', $branchIds)
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();

        $routes = \App\Models\ShuttleRoute::with('branch')
            ->where('is_active', true)
            ->whereIn('branch_id', $branchIds)
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();

        $trips = ShuttleTrip::with(['vehicle', 'route', 'creator', 'branchMovements.branch'])
            ->whereIn('branch_id', $branchIds)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('trip_date', $date->toDateString())
            ->orderBy('shift')
            ->orderBy('arrival_time')
            ->get();

        $totalArrival = $trips->sum('arrival_count');
        $totalDeparture = $trips->sum('departure_count');
        $totalTrips = $trips->count();

        return view('modules.shuttle.operations.index', compact(
            'trips',
            'vehicles',
            'routes',
            'branches',
            'branchId',
            'date',
            'totalArrival',
            'totalDeparture',
            'totalTrips'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $branchIds = $user->visibleBranchIds();
        [$data, $vehicle, $branchMovements] = $this->validateTripPayload($request, $branchIds);

        $data['created_by'] = $user->id;

        DB::transaction(function () use ($data, $branchMovements) {
            $trip = ShuttleTrip::create($data);
            $this->syncBranchMovements($trip, $branchMovements);
        });

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $vehicle->branch_id,
            'date' => $data['trip_date'],
        ])->with('success', 'Sefer kaydedildi.');
    }

    public function edit(ShuttleTrip $operation)
    {
        $user = Auth::user();
        $branchIds = $user->visibleBranchIds();
        abort_unless(in_array((int) $operation->branch_id, array_map('intval', $branchIds), true), 403);

        $vehicles = ShuttleVehicle::with(['branch', 'routes'])
            ->where('is_active', true)
            ->whereIn('branch_id', $branchIds)
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();

        $routes = \App\Models\ShuttleRoute::with('branch')
            ->where('is_active', true)
            ->whereIn('branch_id', $branchIds)
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();

        $branches = Branch::where('is_active', true)->whereIn('id', $branchIds)->get();
        $shifts = ShuttleTrip::SHIFTS;

        $operation->load('branchMovements');

        return view('modules.shuttle.operations.edit', compact(
            'operation',
            'vehicles',
            'routes',
            'branches',
            'shifts'
        ));
    }

    public function update(Request $request, ShuttleTrip $operation)
    {
        $branchIds = Auth::user()->visibleBranchIds();
        abort_unless(in_array((int) $operation->branch_id, array_map('intval', $branchIds), true), 403);
        [$data, $vehicle, $branchMovements] = $this->validateTripPayload($request, $branchIds, $operation);

        DB::transaction(function () use ($operation, $data, $branchMovements) {
            $operation->update($data);
            $this->syncBranchMovements($operation, $branchMovements);
        });

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $vehicle->branch_id,
            'date' => $data['trip_date'],
        ])->with('success', 'Sefer guncellendi.');
    }

    public function departure(Request $request, ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleBranchIds());
        abort_unless(in_array((int) $operation->branch_id, $visibleBranchIds, true), 403);

        $data = $request->validate([
            'departure_time' => 'nullable|date_format:H:i',
            'departure_count' => 'nullable|integer|min:0|max:500',
            'branch_movements' => 'nullable|array',
            'branch_movements.*.departure' => 'nullable|integer|min:0|max:500',
        ]);

        $departureMovements = $this->normaliseBranchMovements(
            $request->input('branch_movements', []),
            $visibleBranchIds,
            ['departure']
        );

        $totals = $this->calculateMovementTotals($departureMovements);
        $data['departure_count'] = $totals['departure'];

        DB::transaction(function () use ($operation, $data, $departureMovements) {
            $operation->update([
                'departure_time' => $data['departure_time'] ?? null,
                'departure_count' => $data['departure_count'],
            ]);

            $operation->branchMovements()
                ->where('movement_type', 'departure')
                ->delete();

            if ($departureMovements !== []) {
                $operation->branchMovements()->createMany($departureMovements);
            }
        });

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $operation->branch_id,
            'date' => $operation->trip_date->toDateString(),
        ])->with('success', 'Donus bilgisi kaydedildi.');
    }

    public function destroy(ShuttleTrip $operation)
    {
        abort_unless(in_array((int) $operation->branch_id, array_map('intval', Auth::user()->visibleBranchIds()), true), 403);
        $branchId = $operation->branch_id;
        $date = $operation->trip_date->toDateString();
        $operation->delete();

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $branchId,
            'date' => $date,
        ])->with('success', 'Sefer silindi.');
    }

    private function validateTripPayload(Request $request, array $visibleBranchIds, ?ShuttleTrip $operation = null): array
    {
        $data = $request->validate([
            'shuttle_vehicle_id' => 'required|exists:shuttle_vehicles,id',
            'route_id' => 'nullable|exists:shuttle_routes,id',
            'branch_id' => 'required|exists:branches,id',
            'shift' => 'required|in:' . implode(',', ShuttleTrip::SHIFTS),
            'trip_date' => 'required|date',
            'arrival_time' => 'nullable|date_format:H:i',
            'arrival_count' => 'nullable|integer|min:0|max:500',
            'departure_time' => 'nullable|date_format:H:i',
            'departure_count' => 'nullable|integer|min:0|max:500',
            'notes' => 'nullable|string|max:500',
            'arrived_with_different_vehicle' => 'nullable|boolean',
            'is_transfer' => 'nullable|boolean',
            'branch_movements' => 'nullable|array',
            'branch_movements.*.arrival' => 'nullable|integer|min:0|max:500',
            'branch_movements.*.departure' => 'nullable|integer|min:0|max:500',
        ]);

        $visibleBranchIds = array_map('intval', $visibleBranchIds);
        if (! in_array((int) $data['branch_id'], $visibleBranchIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Bu sube icin islem yapma yetkin yok.',
            ]);
        }

        $vehicle = ShuttleVehicle::with('routes')
            ->whereIn('branch_id', $visibleBranchIds)
            ->find($data['shuttle_vehicle_id']);

        if (! $vehicle) {
            throw ValidationException::withMessages([
                'shuttle_vehicle_id' => 'Secilen araca erisim iznin yok.',
            ]);
        }

        if ((int) $vehicle->branch_id !== (int) $data['branch_id']) {
            throw ValidationException::withMessages([
                'shuttle_vehicle_id' => 'Secilen arac yalnizca kendi subesi icin kullanilabilir.',
            ]);
        }

        $routeId = $data['route_id'] ? (int) $data['route_id'] : null;
        if ($routeId !== null) {
            $allowedRouteIds = $vehicle->routes->pluck('id')->map(fn ($id) => (int) $id)->all();
            $legacyAllowedRouteId = (
                $operation
                && (int) $operation->shuttle_vehicle_id === (int) $vehicle->id
                && $operation->route_id
            ) ? (int) $operation->route_id : null;

            if (! in_array($routeId, $allowedRouteIds, true) && $routeId !== $legacyAllowedRouteId) {
                throw ValidationException::withMessages([
                    'route_id' => 'Secilen arac bu guzergah icin gorevli degil.',
                ]);
            }
        }

        $data['arrived_with_different_vehicle'] = $request->boolean('arrived_with_different_vehicle');
        $data['is_transfer'] = $request->boolean('is_transfer');

        $branchMovements = $this->normaliseBranchMovements(
            $request->input('branch_movements', []),
            $visibleBranchIds
        );

        $totals = $this->calculateMovementTotals($branchMovements);
        $data['arrival_count'] = $totals['arrival'];
        $data['departure_count'] = $totals['departure'];

        return [$data, $vehicle, $branchMovements];
    }

    private function normaliseBranchMovements(array $rawMovements, array $visibleBranchIds, array $movementTypes = ['arrival', 'departure']): array
    {
        $movements = [];

        foreach ($rawMovements as $branchId => $counts) {
            $branchId = (int) $branchId;
            if (! in_array($branchId, $visibleBranchIds, true)) {
                continue;
            }

            foreach ($movementTypes as $movementType) {
                $value = isset($counts[$movementType]) ? (int) $counts[$movementType] : 0;
                if ($value <= 0) {
                    continue;
                }

                $movements[] = [
                    'branch_id' => $branchId,
                    'movement_type' => $movementType,
                    'headcount' => $value,
                ];
            }
        }

        return $movements;
    }

    private function calculateMovementTotals(array $movements): array
    {
        return [
            'arrival' => (int) collect($movements)
                ->where('movement_type', 'arrival')
                ->sum('headcount'),
            'departure' => (int) collect($movements)
                ->where('movement_type', 'departure')
                ->sum('headcount'),
        ];
    }

    private function syncBranchMovements(ShuttleTrip $trip, array $movements): void
    {
        $trip->branchMovements()->delete();

        if ($movements === []) {
            return;
        }

        $trip->branchMovements()->createMany($movements);
    }
}
