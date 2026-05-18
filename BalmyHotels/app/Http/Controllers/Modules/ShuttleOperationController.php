<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\Branch;
use App\Models\ShuttleRoute;
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
            ['index', 'departure'],
            [],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $visibleBranchIds = array_map('intval', $user->visibleBranchIds());
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $visibleBranchIds)
            ->orderBy('name')
            ->get();
        $allBranches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $currentBranchId = $request->branch_id ?? ($branches->count() === 1 ? $branches->first()->id : null);
        $currentBranchId = $currentBranchId ? (int) $currentBranchId : null;

        $vehicles = ShuttleVehicle::with(['branch', 'routes'])
            ->where('is_active', true)
            ->whereIn('branch_id', $visibleBranchIds)
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();

        $routes = ShuttleRoute::with('branch')
            ->where('is_active', true)
            ->whereIn('branch_id', $visibleBranchIds)
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();

        $trips = ShuttleTrip::with(['vehicle', 'route', 'branch', 'creator', 'branchMovements.branch'])
            ->where('trip_date', $date->toDateString())
            ->where(function ($query) use ($visibleBranchIds, $currentBranchId) {
                if ($currentBranchId !== null) {
                    $query->where('branch_id', $currentBranchId)
                        ->orWhereHas('branchMovements', function ($movementQuery) use ($currentBranchId) {
                            $movementQuery->where('branch_id', $currentBranchId);
                        });

                    return;
                }

                $query->whereIn('branch_id', $visibleBranchIds)
                    ->orWhereHas('branchMovements', function ($movementQuery) use ($visibleBranchIds) {
                        $movementQuery->whereIn('branch_id', $visibleBranchIds);
                    });
            })
            ->orderBy('shift')
            ->orderBy('arrival_time')
            ->get();

        [$totalIncoming, $totalOutgoing] = $this->summariseTripsForContext($trips, $currentBranchId, $visibleBranchIds);
        $totalTrips = $trips->count();

        return view('modules.shuttle.operations.index', compact(
            'trips',
            'vehicles',
            'routes',
            'branches',
            'allBranches',
            'currentBranchId',
            'date',
            'totalIncoming',
            'totalOutgoing',
            'totalTrips'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $visibleBranchIds = array_map('intval', $user->visibleBranchIds());
        [$tripData, $movementMatrix] = $this->validateOwnerPayload($request, $visibleBranchIds);
        $tripData['created_by'] = $user->id;

        DB::transaction(function () use ($tripData, $movementMatrix) {
            $trip = ShuttleTrip::create($tripData);
            $this->saveMovementMatrix($trip, $movementMatrix);
        });

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $tripData['branch_id'],
            'date' => $tripData['trip_date'],
        ])->with('success', 'Sefer kaydedildi. Dahil edilen oteller kendi indi / bindi bilgisini ayri ayri girebilir.');
    }

    public function edit(Request $request, ShuttleTrip $operation)
    {
        $user = Auth::user();
        $visibleBranchIds = array_map('intval', $user->visibleBranchIds());
        abort_unless($this->canAccessTrip($operation, $visibleBranchIds), 403);

        $contextBranchId = $this->resolveContextBranchId(
            $operation,
            $visibleBranchIds,
            $request->filled('branch_id') ? (int) $request->branch_id : null
        );
        $isOwner = $this->isOwnerContext($operation, $contextBranchId);

        $vehicles = ShuttleVehicle::with(['branch', 'routes'])
            ->where('is_active', true)
            ->whereIn('branch_id', $visibleBranchIds)
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();

        $routes = ShuttleRoute::with('branch')
            ->where('is_active', true)
            ->whereIn('branch_id', $visibleBranchIds)
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();

        $branches = Branch::where('is_active', true)
            ->whereIn('id', $visibleBranchIds)
            ->orderBy('name')
            ->get();
        $allBranches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();
        $shifts = ShuttleTrip::SHIFTS;

        $operation->load(['branch', 'creator', 'vehicle', 'route', 'branchMovements.branch']);

        return view('modules.shuttle.operations.edit', [
            'operation' => $operation,
            'vehicles' => $vehicles,
            'routes' => $routes,
            'branches' => $branches,
            'allBranches' => $allBranches,
            'shifts' => $shifts,
            'contextBranchId' => $contextBranchId,
            'isOwner' => $isOwner,
        ]);
    }

    public function update(Request $request, ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleBranchIds());
        abort_unless($this->canAccessTrip($operation, $visibleBranchIds), 403);

        $contextBranchId = $this->resolveContextBranchId(
            $operation,
            $visibleBranchIds,
            $request->filled('context_branch_id') ? (int) $request->context_branch_id : null
        );

        if ($this->isOwnerContext($operation, $contextBranchId)) {
            [$tripData, $movementMatrix] = $this->validateOwnerPayload($request, $visibleBranchIds, $operation);
            $updatedTrip = null;

            DB::transaction(function () use ($operation, $tripData, $movementMatrix, &$updatedTrip) {
                $operation->update($tripData);
                $this->saveMovementMatrix($operation->fresh(), $movementMatrix);
                $updatedTrip = $operation->fresh();
            });

            return redirect()->route('shuttle.operations.index', [
                'branch_id' => $updatedTrip?->branch_id ?? $contextBranchId,
                'date' => $updatedTrip?->trip_date?->toDateString() ?? $operation->trip_date->toDateString(),
            ])->with('success', 'Sefer ve kendi otel hareketlerin guncellendi.');
        }

        $this->updateBranchCounts($request, $operation, $contextBranchId);

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $contextBranchId,
            'date' => $operation->trip_date->toDateString(),
        ])->with('success', 'Kendi otel satirin guncellendi.');
    }

    public function departure(Request $request, ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleBranchIds());
        abort_unless($this->canAccessTrip($operation, $visibleBranchIds), 403);

        $contextBranchId = $this->resolveContextBranchId(
            $operation,
            $visibleBranchIds,
            $request->filled('context_branch_id') ? (int) $request->context_branch_id : null
        );

        $this->updateBranchCounts($request, $operation, $contextBranchId);

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $contextBranchId,
            'date' => $operation->trip_date->toDateString(),
        ])->with('success', 'Kendi otel icin indi / bindi bilgisi kaydedildi.');
    }

    public function destroy(ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleBranchIds());
        abort_unless(in_array((int) $operation->branch_id, $visibleBranchIds, true) || Auth::user()->isSuperAdmin(), 403);

        $branchId = $operation->branch_id;
        $date = $operation->trip_date->toDateString();
        $operation->delete();

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $branchId,
            'date' => $date,
        ])->with('success', 'Sefer silindi.');
    }

    private function validateOwnerPayload(Request $request, array $visibleBranchIds, ?ShuttleTrip $operation = null): array
    {
        $data = $request->validate([
            'shuttle_vehicle_id' => 'required|exists:shuttle_vehicles,id',
            'route_id' => 'nullable|exists:shuttle_routes,id',
            'branch_id' => 'required|exists:branches,id',
            'shift' => 'required|in:' . implode(',', ShuttleTrip::SHIFTS),
            'trip_date' => 'required|date',
            'arrival_time' => 'nullable|date_format:H:i',
            'departure_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
            'arrived_with_different_vehicle' => 'nullable|boolean',
            'is_transfer' => 'nullable|boolean',
            'involved_branch_ids' => 'required|array|min:1',
            'involved_branch_ids.*' => 'required|integer|exists:branches,id',
            'branch_movements' => 'nullable|array',
            'branch_movements.*.arrival' => 'nullable|integer|min:0|max:500',
            'branch_movements.*.departure' => 'nullable|integer|min:0|max:500',
        ]);

        $data['route_id'] = $data['route_id'] ?? null;
        $data['arrival_time'] = $data['arrival_time'] ?? null;
        $data['departure_time'] = $data['departure_time'] ?? null;
        $data['notes'] = $data['notes'] ?? null;
        $data['arrived_with_different_vehicle'] = $request->boolean('arrived_with_different_vehicle');
        $data['is_transfer'] = $request->boolean('is_transfer');

        if (! in_array((int) $data['branch_id'], $visibleBranchIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Bu seferi secili otel adina planlayamazsin.',
            ]);
        }

        $vehicle = ShuttleVehicle::with('routes')
            ->whereIn('branch_id', $visibleBranchIds)
            ->find($data['shuttle_vehicle_id']);

        if (! $vehicle || (int) $vehicle->branch_id !== (int) $data['branch_id']) {
            throw ValidationException::withMessages([
                'shuttle_vehicle_id' => 'Secilen arac yalnizca kendi otelinin seferinde kullanilabilir.',
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

        $selectedBranchIds = collect($data['involved_branch_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $existingMatrix = $operation ? $this->branchMovementMatrix($operation) : [];
        $preservedBranchIds = [];

        foreach ($existingMatrix as $branchId => $counts) {
            if (in_array((int) $branchId, $visibleBranchIds, true)) {
                continue;
            }

            if (((int) $counts['arrival'] > 0 || (int) $counts['departure'] > 0) && ! in_array((int) $branchId, $selectedBranchIds, true)) {
                $preservedBranchIds[] = (int) $branchId;
            }
        }

        $finalBranchIds = collect(array_merge($selectedBranchIds, $preservedBranchIds))
            ->unique()
            ->values()
            ->all();

        $movementMatrix = [];
        foreach ($finalBranchIds as $branchId) {
            $existingCounts = $existingMatrix[$branchId] ?? ['arrival' => 0, 'departure' => 0];
            $arrival = in_array($branchId, $visibleBranchIds, true)
                ? (int) data_get($request->input("branch_movements.$branchId", []), 'arrival', 0)
                : (int) $existingCounts['arrival'];
            $departure = in_array($branchId, $visibleBranchIds, true)
                ? (int) data_get($request->input("branch_movements.$branchId", []), 'departure', 0)
                : (int) $existingCounts['departure'];

            $movementMatrix[$branchId] = [
                'arrival' => $arrival,
                'departure' => $departure,
            ];
        }

        return [
            collect($data)->except(['involved_branch_ids', 'branch_movements'])->all(),
            $movementMatrix,
        ];
    }

    private function updateBranchCounts(Request $request, ShuttleTrip $operation, int $contextBranchId): void
    {
        $data = $request->validate([
            'arrival_count' => 'required|integer|min:0|max:500',
            'departure_count' => 'required|integer|min:0|max:500',
        ]);

        $movementMatrix = $this->branchMovementMatrix($operation);
        $movementMatrix[$contextBranchId] = [
            'arrival' => (int) $data['arrival_count'],
            'departure' => (int) $data['departure_count'],
        ];

        DB::transaction(function () use ($operation, $movementMatrix) {
            $this->saveMovementMatrix($operation->fresh(), $movementMatrix);
        });
    }

    private function saveMovementMatrix(ShuttleTrip $trip, array $movementMatrix): void
    {
        $records = [];
        $totalArrival = 0;
        $totalDeparture = 0;

        foreach ($movementMatrix as $branchId => $counts) {
            $branchId = (int) $branchId;
            $arrival = (int) ($counts['arrival'] ?? 0);
            $departure = (int) ($counts['departure'] ?? 0);

            $records[] = [
                'branch_id' => $branchId,
                'movement_type' => 'arrival',
                'headcount' => $arrival,
            ];
            $records[] = [
                'branch_id' => $branchId,
                'movement_type' => 'departure',
                'headcount' => $departure,
            ];

            $totalArrival += $arrival;
            $totalDeparture += $departure;
        }

        $trip->update([
            'arrival_count' => $totalArrival,
            'departure_count' => $totalDeparture,
        ]);

        $trip->branchMovements()->delete();

        if ($records !== []) {
            $trip->branchMovements()->createMany($records);
        }
    }

    private function canAccessTrip(ShuttleTrip $trip, array $visibleBranchIds): bool
    {
        if (Auth::user()->isSuperAdmin()) {
            return true;
        }

        if (in_array((int) $trip->branch_id, $visibleBranchIds, true)) {
            return true;
        }

        return $trip->branchMovements()
            ->whereIn('branch_id', $visibleBranchIds)
            ->exists();
    }

    private function resolveContextBranchId(ShuttleTrip $trip, array $visibleBranchIds, ?int $preferredBranchId = null): int
    {
        if ($preferredBranchId !== null && in_array($preferredBranchId, $visibleBranchIds, true)) {
            if ((int) $trip->branch_id === $preferredBranchId) {
                return $preferredBranchId;
            }

            if ($trip->branchMovements()->where('branch_id', $preferredBranchId)->exists()) {
                return $preferredBranchId;
            }
        }

        if (in_array((int) $trip->branch_id, $visibleBranchIds, true)) {
            return (int) $trip->branch_id;
        }

        foreach ($visibleBranchIds as $branchId) {
            if ($trip->branchMovements()->where('branch_id', $branchId)->exists()) {
                return (int) $branchId;
            }
        }

        abort(403);
    }

    private function isOwnerContext(ShuttleTrip $trip, int $contextBranchId): bool
    {
        return (int) $trip->branch_id === $contextBranchId || Auth::user()->isSuperAdmin();
    }

    private function branchMovementMatrix(ShuttleTrip $trip): array
    {
        $trip->loadMissing('branchMovements');

        return $trip->branchMovements
            ->groupBy('branch_id')
            ->map(function ($items) {
                return [
                    'arrival' => (int) optional($items->firstWhere('movement_type', 'arrival'))->headcount,
                    'departure' => (int) optional($items->firstWhere('movement_type', 'departure'))->headcount,
                ];
            })
            ->toArray();
    }

    private function summariseTripsForContext($trips, ?int $currentBranchId, array $visibleBranchIds): array
    {
        $incoming = 0;
        $outgoing = 0;

        foreach ($trips as $trip) {
            if ($currentBranchId !== null) {
                $incoming += (int) optional(
                    $trip->branchMovements->first(
                        fn ($movement) => (int) $movement->branch_id === $currentBranchId && $movement->movement_type === 'arrival'
                    )
                )->headcount;
                $outgoing += (int) optional(
                    $trip->branchMovements->first(
                        fn ($movement) => (int) $movement->branch_id === $currentBranchId && $movement->movement_type === 'departure'
                    )
                )->headcount;
                continue;
            }

            foreach ($visibleBranchIds as $branchId) {
                $incoming += (int) optional(
                    $trip->branchMovements->first(
                        fn ($movement) => (int) $movement->branch_id === $branchId && $movement->movement_type === 'arrival'
                    )
                )->headcount;
                $outgoing += (int) optional(
                    $trip->branchMovements->first(
                        fn ($movement) => (int) $movement->branch_id === $branchId && $movement->movement_type === 'departure'
                    )
                )->headcount;
            }
        }

        return [$incoming, $outgoing];
    }
}
