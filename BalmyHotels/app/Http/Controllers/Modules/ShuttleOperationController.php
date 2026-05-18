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
        $visibleBranchIds = array_map('intval', $user->visibleBranchIds());
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $visibleBranchIds)
            ->orderBy('name')
            ->get();

        $destinationBranches = Branch::where('is_active', true)
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

        $trips = ShuttleTrip::with([
                'vehicle',
                'route',
                'branch',
                'destinationBranch',
                'creator',
                'branchMovements.branch',
            ])
            ->where(function ($query) use ($visibleBranchIds) {
                $query->whereIn('branch_id', $visibleBranchIds)
                    ->orWhereIn('destination_branch_id', $visibleBranchIds);
            })
            ->when($currentBranchId, function ($query) use ($currentBranchId) {
                $query->where(function ($subQuery) use ($currentBranchId) {
                    $subQuery->where('branch_id', $currentBranchId)
                        ->orWhere('destination_branch_id', $currentBranchId);
                });
            })
            ->where('trip_date', $date->toDateString())
            ->orderBy('shift')
            ->orderBy('origin_departure_time')
            ->orderBy('arrival_time')
            ->get();

        [$totalIncoming, $totalOutgoing] = $this->summariseTripsForBranchContext($trips, $currentBranchId, $visibleBranchIds);
        $totalTrips = $trips->count();

        return view('modules.shuttle.operations.index', compact(
            'trips',
            'vehicles',
            'routes',
            'branches',
            'destinationBranches',
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
        $data = $this->validateOriginPayload($request, $visibleBranchIds);
        $data['created_by'] = $user->id;
        $data['arrival_count'] = $data['origin_departure_count'];
        $data['departure_count'] = 0;
        $data['arrival_time'] = null;
        $data['departure_time'] = null;

        DB::transaction(function () use ($data) {
            $trip = ShuttleTrip::create($data);
            $this->syncTripBranchMovements($trip);
        });

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $data['branch_id'],
            'date' => $data['trip_date'],
        ])->with('success', 'Sefer kaydedildi. Karsi otel kendi inen/binen bilgisini bu kayit uzerinden isleyecek.');
    }

    public function edit(Request $request, ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleBranchIds());
        $contextBranchId = $request->filled('branch_id') ? (int) $request->branch_id : null;
        $contextRole = $this->resolveTripContext($operation, $visibleBranchIds, $contextBranchId);

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

        $destinationBranches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        $shifts = ShuttleTrip::SHIFTS;

        $operation->load(['branch', 'destinationBranch', 'branchMovements.branch']);

        return view('modules.shuttle.operations.edit', compact(
            'operation',
            'vehicles',
            'routes',
            'branches',
            'destinationBranches',
            'shifts',
            'contextRole',
            'contextBranchId'
        ));
    }

    public function update(Request $request, ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleBranchIds());
        $contextRole = $this->resolveTripContext(
            $operation,
            $visibleBranchIds,
            $request->filled('context_branch_id') ? (int) $request->context_branch_id : null
        );

        if ($contextRole === 'origin') {
            $this->updateOriginSide($request, $operation, $visibleBranchIds);

            return redirect()->route('shuttle.operations.index', [
                'branch_id' => $operation->branch_id,
                'date' => $operation->trip_date->toDateString(),
            ])->with('success', 'Kaynak otel kaydi guncellendi.');
        }

        $this->updateDestinationSide($request, $operation);

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $operation->destination_branch_id,
            'date' => $operation->trip_date->toDateString(),
        ])->with('success', 'Karsi otel inen/binen bilgisi guncellendi.');
    }

    public function departure(Request $request, ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleBranchIds());
        $contextRole = $this->resolveTripContext(
            $operation,
            $visibleBranchIds,
            $request->filled('context_branch_id') ? (int) $request->context_branch_id : null
        );

        if ($contextRole !== 'destination') {
            abort(403);
        }

        $this->updateDestinationSide($request, $operation);

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $operation->destination_branch_id,
            'date' => $operation->trip_date->toDateString(),
        ])->with('success', 'Karsi otel inen/binen bilgisi kaydedildi.');
    }

    public function destroy(ShuttleTrip $operation)
    {
        $user = Auth::user();
        $visibleBranchIds = array_map('intval', $user->visibleBranchIds());

        if (! $user->isSuperAdmin() && ! in_array((int) $operation->branch_id, $visibleBranchIds, true)) {
            abort(403);
        }

        $branchId = $operation->branch_id;
        $date = $operation->trip_date->toDateString();
        $operation->delete();

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $branchId,
            'date' => $date,
        ])->with('success', 'Sefer silindi.');
    }

    private function updateOriginSide(Request $request, ShuttleTrip $operation, array $visibleBranchIds): void
    {
        $oldOriginCount = (int) $operation->origin_departure_count;
        $oldArrivalCount = (int) $operation->arrival_count;
        $data = $this->validateOriginPayload($request, $visibleBranchIds, $operation);

        DB::transaction(function () use ($operation, $data, $oldOriginCount, $oldArrivalCount) {
            $payload = collect($data)->only([
                'shuttle_vehicle_id',
                'route_id',
                'branch_id',
                'destination_branch_id',
                'shift',
                'trip_date',
                'origin_departure_time',
                'origin_departure_count',
                'notes',
                'arrived_with_different_vehicle',
                'is_transfer',
            ])->all();

            if (
                ! $operation->arrival_time
                && ($oldArrivalCount === 0 || $oldArrivalCount === $oldOriginCount)
            ) {
                $payload['arrival_count'] = (int) $data['origin_departure_count'];
            }

            $operation->update($payload);
            $this->syncTripBranchMovements($operation->fresh());
        });
    }

    private function updateDestinationSide(Request $request, ShuttleTrip $operation): void
    {
        $data = $request->validate([
            'arrival_time' => 'nullable|date_format:H:i',
            'arrival_count' => 'required|integer|min:0|max:500',
            'departure_time' => 'nullable|date_format:H:i',
            'departure_count' => 'required|integer|min:0|max:500',
        ]);

        DB::transaction(function () use ($operation, $data) {
            $operation->update($data);
            $this->syncTripBranchMovements($operation->fresh());
        });
    }

    private function validateOriginPayload(Request $request, array $visibleBranchIds, ?ShuttleTrip $operation = null): array
    {
        $data = $request->validate([
            'shuttle_vehicle_id' => 'required|exists:shuttle_vehicles,id',
            'route_id' => 'nullable|exists:shuttle_routes,id',
            'branch_id' => 'required|exists:branches,id',
            'destination_branch_id' => 'required|exists:branches,id|different:branch_id',
            'shift' => 'required|in:' . implode(',', ShuttleTrip::SHIFTS),
            'trip_date' => 'required|date',
            'origin_departure_time' => 'nullable|date_format:H:i',
            'origin_departure_count' => 'required|integer|min:0|max:500',
            'notes' => 'nullable|string|max:500',
            'arrived_with_different_vehicle' => 'nullable|boolean',
            'is_transfer' => 'nullable|boolean',
        ]);

        $data['route_id'] = $data['route_id'] ?? null;
        $data['origin_departure_time'] = $data['origin_departure_time'] ?? null;
        $data['notes'] = $data['notes'] ?? null;

        if (! in_array((int) $data['branch_id'], $visibleBranchIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Bu otel icin islem yapma yetkin yok.',
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
                'shuttle_vehicle_id' => 'Secilen arac yalnizca kendi oteli tarafindan planlanabilir.',
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

        return $data;
    }

    private function resolveTripContext(ShuttleTrip $trip, array $visibleBranchIds, ?int $preferredBranchId = null): string
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            if ($preferredBranchId && $trip->isDestinationForBranch($preferredBranchId)) {
                return 'destination';
            }

            return 'origin';
        }

        if ($preferredBranchId !== null) {
            if ($trip->isOriginForBranch($preferredBranchId)) {
                return 'origin';
            }

            if ($trip->isDestinationForBranch($preferredBranchId)) {
                return 'destination';
            }
        }

        foreach ($visibleBranchIds as $branchId) {
            if ($trip->isOriginForBranch($branchId)) {
                return 'origin';
            }

            if ($trip->isDestinationForBranch($branchId)) {
                return 'destination';
            }
        }

        abort(403);
    }

    private function syncTripBranchMovements(ShuttleTrip $trip): void
    {
        $movements = [];

        if ((int) $trip->origin_departure_count > 0) {
            $movements[] = [
                'branch_id' => $trip->branch_id,
                'movement_type' => 'departure',
                'headcount' => (int) $trip->origin_departure_count,
            ];
        }

        if ($trip->destination_branch_id && (int) $trip->arrival_count > 0) {
            $movements[] = [
                'branch_id' => $trip->destination_branch_id,
                'movement_type' => 'arrival',
                'headcount' => (int) $trip->arrival_count,
            ];
        }

        if ($trip->destination_branch_id && (int) $trip->departure_count > 0) {
            $movements[] = [
                'branch_id' => $trip->destination_branch_id,
                'movement_type' => 'departure',
                'headcount' => (int) $trip->departure_count,
            ];
        }

        $trip->branchMovements()->delete();

        if ($movements !== []) {
            $trip->branchMovements()->createMany($movements);
        }
    }

    private function summariseTripsForBranchContext($trips, ?int $currentBranchId, array $visibleBranchIds): array
    {
        $incoming = 0;
        $outgoing = 0;

        foreach ($trips as $trip) {
            if ($currentBranchId !== null) {
                $summary = $trip->branchContextSummary($currentBranchId);
                $incoming += $summary['incoming'];
                $outgoing += $summary['outgoing'];
                continue;
            }

            foreach ($visibleBranchIds as $branchId) {
                $summary = $trip->branchContextSummary((int) $branchId);
                $incoming += $summary['incoming'];
                $outgoing += $summary['outgoing'];
            }
        }

        return [$incoming, $outgoing];
    }
}
