<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Modules\BaseModuleController;
use App\Models\Branch;
use App\Models\ShuttleRoute;
use App\Models\ShuttleTrip;
use App\Models\ShuttleTripBranchCompletion;
use App\Models\ShuttleTripBranchMovement;
use App\Models\ShuttleVehicle;
use App\Services\ShuttleTripMergeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShuttleOperationController extends BaseModuleController
{
    private const OPERATION_DAY_START_TIME = '08:00';

    private ShuttleTripMergeService $tripMergeService;

    public function __construct()
    {
        $this->tripMergeService = app(ShuttleTripMergeService::class);

        $this->requirePermission(
            'shuttle_operations',
            ['index', 'departure', 'complete'],
            [],
            ['create', 'store', 'storeLodging'],
            ['edit', 'update'],
            ['destroy']
        );
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $visibleBranchIds = array_map('intval', $user->visibleShuttleBranchIds());
        $branches = Branch::where('is_active', true)
            ->whereIn('id', $visibleBranchIds)
            ->orderBy('name')
            ->get();
        $allBranches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        $date = $request->date ? Carbon::parse($request->date) : $this->currentOperationDate();
        $currentBranchId = $request->branch_id ?? ($branches->count() === 1 ? $branches->first()->id : null);
        $currentBranchId = $currentBranchId ? (int) $currentBranchId : null;
        $showCompleted = $request->boolean('show_completed');
        $defaultLodgingDate = $this->defaultLodgingDateForOperationDate($date);

        $vehicles = ShuttleVehicle::with('routes')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $listStartDate = $showCompleted ? $date->copy() : $date->copy()->subDay();
        $listEndDate = $date->copy()->addDay();
        $listQueryEndDate = $listEndDate->copy()->addDay();

        $summaryTrips = ShuttleTrip::with(['vehicle.routes', 'route', 'branch', 'creator', 'branchMovements.branch'])
            ->whereBetween('trip_date', [$date->toDateString(), $date->copy()->addDay()->toDateString()])
            ->orderBy('shift')
            ->orderBy('arrival_time')
            ->get();
        $summaryTrips = $this->tripMergeService->mergeCollection($summaryTrips);
        $summaryTrips = $this->filterTripsForOperationDateRange($summaryTrips, $date, $date);
        $summaryTrips = $this->sortTripsForOperation($summaryTrips, $currentBranchId);

        $completedTripCompletionsForContext = $this->completedTripCompletionsForContext($currentBranchId, $listStartDate, $listEndDate);
        $completedTripIdsForContext = $completedTripCompletionsForContext
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        $trips = ShuttleTrip::with(['vehicle.routes', 'route', 'branch', 'creator', 'branchMovements.branch'])
            ->whereBetween('trip_date', [$listStartDate->toDateString(), $listQueryEndDate->toDateString()])
            ->orderBy('trip_date')
            ->orderBy('shift')
            ->orderBy('arrival_time')
            ->get();
        $trips = $this->tripMergeService->mergeCollection($trips);
        $trips = $this->filterTripsForOperationDateRange($trips, $listStartDate, $listEndDate);
        $trips = $this->sortTripsForOperation($trips, $currentBranchId);
        $trips = $this->filterTripsForCompletion($trips, $currentBranchId, $showCompleted, $completedTripIdsForContext);
        $trips = $this->annotateTripsWithOperationDate($trips);

        [
            $totalIncoming,
            $totalOutgoing,
            $totalLodgingIncoming,
            $totalLodgingOutgoing,
            $totalLodgingTrips,
        ] = $this->summariseTripsForContext($summaryTrips, $currentBranchId, $visibleBranchIds);
        $totalTrips = $this->countServiceTripsForContext($summaryTrips, $currentBranchId, $visibleBranchIds);
        $serviceShifts = $this->serviceShifts();

        return view('modules.shuttle.operations.index', compact(
            'trips',
            'vehicles',
            'branches',
            'allBranches',
            'currentBranchId',
            'date',
            'defaultLodgingDate',
            'listStartDate',
            'listEndDate',
            'showCompleted',
            'completedTripCompletionsForContext',
            'completedTripIdsForContext',
            'totalIncoming',
            'totalOutgoing',
            'totalLodgingIncoming',
            'totalLodgingOutgoing',
            'totalLodgingTrips',
            'totalTrips',
            'serviceShifts'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $visibleBranchIds = array_map('intval', $user->visibleShuttleBranchIds());
        [$tripData, $movementMatrix] = $this->validateOwnerPayload($request, $visibleBranchIds);
        $tripData['created_by'] = $user->id;

        $wasMerged = false;
        DB::transaction(function () use ($tripData, $movementMatrix, &$wasMerged) {
            $trip = ShuttleTrip::query()
                ->where('trip_date', $tripData['trip_date'])
                ->where('shift', $tripData['shift'])
                ->where('shuttle_vehicle_id', $tripData['shuttle_vehicle_id'])
                ->where(function ($query) {
                    $query->where('is_lodging_route', false)
                        ->orWhereNull('is_lodging_route');
                })
                ->orderBy('id')
                ->first();

            if ($trip) {
                $trip = $this->tripMergeService->consolidateTrip($trip);
                $existingMatrix = $this->branchMovementMatrix($trip);
                $mergedMatrix = $this->mergeMovementMatrices($existingMatrix, $movementMatrix);

                $trip->update([
                    'route_id' => $trip->route_id ?: $tripData['route_id'],
                    'notes' => $this->mergeNotes($trip->notes, $tripData['notes'] ?? null),
                    'arrived_with_different_vehicle' => $trip->arrived_with_different_vehicle || ($tripData['arrived_with_different_vehicle'] ?? false),
                    'is_transfer' => $trip->is_transfer || ($tripData['is_transfer'] ?? false),
                    'is_lodging_route' => $trip->is_lodging_route || ($tripData['is_lodging_route'] ?? false),
                ]);

                $this->saveMovementMatrix($trip->fresh(), $mergedMatrix);
                $wasMerged = true;

                return;
            }

            $trip = ShuttleTrip::create($tripData);
            $this->saveMovementMatrix($trip, $movementMatrix);
        });

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $tripData['branch_id'],
            'date' => $this->operationDateForShiftDate($tripData['trip_date'], $tripData['shift'])->toDateString(),
        ])->with('success', $wasMerged
            ? 'Ayni plaka icin mevcut servis hareketi bulundu ve yeni bilgiler onunla birlestirildi.'
            : 'Servis hareketi kaydedildi. Diger otel ayni kaydi gorup kendi saat ve sayi bilgisini isleyebilir.');
    }

    public function storeLodging(Request $request)
    {
        $user = Auth::user();
        $visibleBranchIds = array_map('intval', $user->visibleShuttleBranchIds());

        $data = $request->validate([
            'shuttle_vehicle_id' => 'required|exists:shuttle_vehicles,id',
            'branch_id' => 'required|exists:branches,id',
            'trip_date' => 'required|date',
            'movement_type' => 'required|in:arrival,departure',
            'movement_time' => 'required|date_format:H:i',
            'headcount' => 'required|integer|min:1|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        $branchId = (int) $data['branch_id'];
        if (! in_array($branchId, $visibleBranchIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Bu lojman hareketini secili otel adina planlayamazsin.',
            ]);
        }

        $vehicle = ShuttleVehicle::query()
            ->where('is_active', true)
            ->find((int) $data['shuttle_vehicle_id']);

        if (! $vehicle) {
            throw ValidationException::withMessages([
                'shuttle_vehicle_id' => 'Secilen arac aktif degil ya da bulunamadi.',
            ]);
        }

        $movementType = (string) $data['movement_type'];
        $movementTime = $this->normaliseTime($data['movement_time']);
        $headcount = (int) $data['headcount'];
        $isArrival = $movementType === 'arrival';

        $tripData = [
            'shuttle_vehicle_id' => (int) $data['shuttle_vehicle_id'],
            'route_id' => null,
            'branch_id' => $branchId,
            'shift' => 'Lojman',
            'trip_date' => $data['trip_date'],
            'arrival_time' => $isArrival ? $movementTime : null,
            'arrival_count' => $isArrival ? $headcount : 0,
            'departure_time' => $isArrival ? null : $movementTime,
            'departure_count' => $isArrival ? 0 : $headcount,
            'arrived_with_different_vehicle' => false,
            'is_transfer' => false,
            'is_lodging_route' => true,
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
        ];

        DB::transaction(function () use ($tripData, $branchId, $isArrival, $headcount, $movementTime) {
            $trip = ShuttleTrip::create($tripData);

            $this->saveMovementMatrix($trip, [
                ShuttleTripBranchMovement::DEFAULT_PERIOD => [
                    $branchId => [
                        'arrival' => $isArrival ? $headcount : 0,
                        'departure' => $isArrival ? 0 : $headcount,
                        'movement_time' => null,
                        'arrival_time' => $isArrival ? $movementTime : null,
                        'departure_time' => $isArrival ? null : $movementTime,
                    ],
                ],
            ]);
        });

        $operationDate = $this->operationDateForLodgingData($data['trip_date'], $data['movement_time']);

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $branchId,
            'date' => $operationDate->toDateString(),
        ])->with('success', $isArrival
            ? 'Lojman gelis hareketi kaydedildi.'
            : 'Lojman gidis hareketi kaydedildi.');
    }

    public function edit(Request $request, ShuttleTrip $operation)
    {
        $user = Auth::user();
        $visibleBranchIds = array_map('intval', $user->visibleShuttleBranchIds());
        $operation = $this->tripMergeService->buildDisplayTrip($operation);
        abort_unless($this->canAccessTrip($operation, $visibleBranchIds), 403);

        $contextBranchId = $this->resolveContextBranchId(
            $operation,
            $visibleBranchIds,
            $request->filled('branch_id') ? (int) $request->branch_id : null
        );
        $isOwner = $this->isOwnerContext($operation, $contextBranchId);

        $vehicles = ShuttleVehicle::with('routes')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $routes = ShuttleRoute::where('is_active', true)
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
        if (! $operation->is_lodging_trip) {
            $shifts = $this->serviceShifts();
        }

        $operation->load(['branch', 'creator', 'vehicle', 'route', 'branchMovements.branch']);

        $operationDate = $this->operationDateForTrip($operation);

        return view('modules.shuttle.operations.edit', [
            'operation' => $operation,
            'vehicles' => $vehicles,
            'routes' => $routes,
            'branches' => $branches,
            'allBranches' => $allBranches,
            'shifts' => $shifts,
            'contextBranchId' => $contextBranchId,
            'isOwner' => $isOwner,
            'operationDate' => $operationDate,
        ]);
    }

    public function update(Request $request, ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleShuttleBranchIds());
        $operation = $this->tripMergeService->consolidateTrip($operation);
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
                $updatedTrip = $this->tripMergeService->consolidateTrip($operation->fresh());
            });

            return redirect()->route('shuttle.operations.index', [
                'branch_id' => $updatedTrip?->branch_id ?? $contextBranchId,
                'date' => $this->operationDateForTrip($updatedTrip ?? $operation)->toDateString(),
            ])->with('success', 'Sefer ve kendi otel hareketlerin guncellendi.');
        }

        $this->updateBranchCounts($request, $operation, $contextBranchId);

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $contextBranchId,
            'date' => $this->operationDateForTrip($operation)->toDateString(),
        ])->with('success', 'Kendi otel satirin guncellendi.');
    }

    public function departure(Request $request, ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleShuttleBranchIds());
        $operation = $this->tripMergeService->consolidateTrip($operation);
        abort_unless($this->canAccessTrip($operation, $visibleBranchIds), 403);

        $contextBranchId = $this->resolveContextBranchId(
            $operation,
            $visibleBranchIds,
            $request->filled('context_branch_id') ? (int) $request->context_branch_id : null
        );

        $this->updateBranchCounts($request, $operation, $contextBranchId);

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $contextBranchId,
            'date' => $this->operationDateForTrip($operation)->toDateString(),
        ])->with('success', 'Kendi otel icin indi / bindi bilgisi kaydedildi.');
    }

    public function complete(Request $request, ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleShuttleBranchIds());
        $operation = $this->tripMergeService->consolidateTrip($operation);
        abort_unless($this->canAccessTrip($operation, $visibleBranchIds), 403);

        $data = $request->validate([
            'context_branch_id' => 'required|integer|exists:branches,id',
            'confirm_missing' => 'nullable|boolean',
            'return_date' => 'nullable|date',
        ]);

        $contextBranchId = $this->resolveContextBranchId(
            $operation,
            $visibleBranchIds,
            (int) $data['context_branch_id']
        );

        $hasMissingData = $this->tripHasMissingCompletionData($operation, $contextBranchId);
        if ($hasMissingData && ! $request->boolean('confirm_missing')) {
            throw ValidationException::withMessages([
                'confirm_missing' => 'Bu seferde secili otel icin gelis veya gidis bilgisi eksik. Eksikle kapatmak icin onay verin.',
            ]);
        }

        ShuttleTripBranchCompletion::updateOrCreate(
            [
                'shuttle_trip_id' => $operation->id,
                'branch_id' => $contextBranchId,
            ],
            [
                'completed_at' => now(),
                'completed_by' => Auth::id(),
                'completed_with_missing_data' => $hasMissingData,
            ]
        );

        $returnDate = Carbon::parse($data['return_date'] ?? $this->operationDateForTrip($operation)->toDateString())->toDateString();

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $contextBranchId,
            'date' => $returnDate,
        ])->with('success', $hasMissingData
            ? 'Sefer eksik bilgi onayi ile bitirildi ve hareket listesinden kaldirildi.'
            : 'Sefer bitirildi ve hareket listesinden kaldirildi.');
    }

    public function destroy(ShuttleTrip $operation)
    {
        $visibleBranchIds = array_map('intval', Auth::user()->visibleShuttleBranchIds());
        $operation = $this->tripMergeService->consolidateTrip($operation);
        abort_unless(in_array((int) $operation->branch_id, $visibleBranchIds, true) || Auth::user()->isSuperAdmin(), 403);

        $branchId = $operation->branch_id;
        $date = $this->operationDateForTrip($operation)->toDateString();
        $operation->delete();

        return redirect()->route('shuttle.operations.index', [
            'branch_id' => $branchId,
            'date' => $date,
        ])->with('success', 'Sefer silindi.');
    }

    private function validateOwnerPayload(Request $request, array $visibleBranchIds, ?ShuttleTrip $operation = null): array
    {
        $allowedShifts = ($operation && $operation->is_lodging_trip)
            ? ShuttleTrip::SHIFTS
            : $this->serviceShifts();

        $data = $request->validate([
            'shuttle_vehicle_id' => 'required|exists:shuttle_vehicles,id',
            'route_id' => 'nullable|exists:shuttle_routes,id',
            'branch_id' => 'required|exists:branches,id',
            'shift' => 'required|in:' . implode(',', $allowedShifts),
            'trip_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'arrived_with_different_vehicle' => 'nullable|boolean',
            'is_transfer' => 'nullable|boolean',
            'is_lodging_route' => 'nullable|boolean',
            'involved_branch_ids' => 'required|array|min:1',
            'involved_branch_ids.*' => 'required|integer|exists:branches,id',
            'branch_movements' => 'nullable|array',
            'branch_movements.*' => 'nullable|array',
            'branch_movements.*.*.arrival' => 'nullable|integer|min:0|max:500',
            'branch_movements.*.*.departure' => 'nullable|integer|min:0|max:500',
            'branch_movements.*.*.movement_time' => 'nullable|date_format:H:i',
            'branch_movements.*.*.arrival_time' => 'nullable|date_format:H:i',
            'branch_movements.*.*.departure_time' => 'nullable|date_format:H:i',
        ]);

        $data['route_id'] = $data['route_id'] ?? null;
        $data['arrival_time'] = null;
        $data['departure_time'] = null;
        $data['notes'] = $data['notes'] ?? null;
        $data['arrived_with_different_vehicle'] = $request->boolean('arrived_with_different_vehicle');
        $data['is_transfer'] = $request->boolean('is_transfer');
        $data['is_lodging_route'] = $operation ? (bool) $operation->is_lodging_trip : false;

        if (! $data['is_lodging_route']) {
            $data['trip_date'] = $this->actualTripDateForOperationDate($data['trip_date'], $data['shift'])->toDateString();
        }

        if (! in_array((int) $data['branch_id'], $visibleBranchIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Bu seferi secili otel adina planlayamazsin.',
            ]);
        }

        $vehicle = ShuttleVehicle::with('routes')
            ->where('is_active', true)
            ->find($data['shuttle_vehicle_id']);

        if (! $vehicle) {
            throw ValidationException::withMessages([
                'shuttle_vehicle_id' => 'Secilen arac aktif degil ya da bulunamadi.',
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
            ->push((int) $data['branch_id'])
            ->unique()
            ->values()
            ->all();

        $existingMatrix = $operation ? $this->branchMovementMatrix($operation) : [];
        $preservedBranchIds = [];

        foreach ($existingMatrix as $periodRows) {
            foreach ($periodRows as $branchId => $counts) {
                if (in_array((int) $branchId, $visibleBranchIds, true)) {
                    continue;
                }

                if ($this->movementRowHasData($counts) && ! in_array((int) $branchId, $selectedBranchIds, true)) {
                    $preservedBranchIds[] = (int) $branchId;
                }
            }
        }

        $finalBranchIds = collect(array_merge($selectedBranchIds, $preservedBranchIds))
            ->unique()
            ->values()
            ->all();

        $movementMatrix = [];
        foreach ($this->movementPeriods() as $period) {
            foreach ($finalBranchIds as $branchId) {
                $existingCounts = data_get($existingMatrix, "$period.$branchId", $this->emptyMovementRow());
                $inputRow = (array) $request->input("branch_movements.$period.$branchId", []);
                $canEditBranch = in_array($branchId, $visibleBranchIds, true);
                $movementTime = $canEditBranch
                    ? $this->normaliseMovementTime($inputRow)
                    : ($existingCounts['movement_time'] ?? $existingCounts['arrival_time'] ?? $existingCounts['departure_time'] ?? null);

                $movementMatrix[$period][$branchId] = [
                    'arrival' => $canEditBranch ? (int) data_get($inputRow, 'arrival', 0) : (int) $existingCounts['arrival'],
                    'departure' => $canEditBranch ? (int) data_get($inputRow, 'departure', 0) : (int) $existingCounts['departure'],
                    'movement_time' => $movementTime,
                    'arrival_time' => $movementTime,
                    'departure_time' => $movementTime,
                ];
            }
        }

        return [
            collect($data)->except(['involved_branch_ids', 'branch_movements'])->all(),
            $movementMatrix,
        ];
    }

    private function updateBranchCounts(Request $request, ShuttleTrip $operation, int $contextBranchId): void
    {
        $movementMatrix = $this->branchMovementMatrix($operation);

        if ($request->has('branch_movements')) {
            $request->validate([
                'branch_movements' => 'required|array',
                'branch_movements.*' => 'nullable|array',
                'branch_movements.*.*.arrival' => 'nullable|integer|min:0|max:500',
                'branch_movements.*.*.departure' => 'nullable|integer|min:0|max:500',
                'branch_movements.*.*.movement_time' => 'nullable|date_format:H:i',
                'branch_movements.*.*.arrival_time' => 'nullable|date_format:H:i',
                'branch_movements.*.*.departure_time' => 'nullable|date_format:H:i',
            ]);

            foreach ($this->movementPeriods() as $period) {
                $inputRow = (array) $request->input("branch_movements.$period.$contextBranchId", []);
                $movementTime = $this->normaliseMovementTime($inputRow);
                $movementMatrix[$period][$contextBranchId] = [
                    'arrival' => (int) data_get($inputRow, 'arrival', 0),
                    'departure' => (int) data_get($inputRow, 'departure', 0),
                    'movement_time' => $movementTime,
                    'arrival_time' => $movementTime,
                    'departure_time' => $movementTime,
                ];
            }
        } else {
            $data = $request->validate([
                'movement_period' => 'required|string|in:' . implode(',', $this->movementPeriods()),
                'arrival_count' => 'required|integer|min:0|max:500',
                'departure_count' => 'required|integer|min:0|max:500',
                'movement_time' => 'nullable|date_format:H:i',
                'arrival_time' => 'nullable|date_format:H:i',
                'departure_time' => 'nullable|date_format:H:i',
            ]);

            $period = $this->normalisePeriod($data['movement_period'] ?? null);
            $movementTime = $this->normaliseMovementTime($data);
            $movementMatrix[$period][$contextBranchId] = [
                'arrival' => (int) $data['arrival_count'],
                'departure' => (int) $data['departure_count'],
                'movement_time' => $movementTime,
                'arrival_time' => $movementTime,
                'departure_time' => $movementTime,
            ];
        }

        DB::transaction(function () use ($operation, $movementMatrix) {
            $this->saveMovementMatrix($operation->fresh(), $movementMatrix);
        });
    }

    private function saveMovementMatrix(ShuttleTrip $trip, array $movementMatrix): void
    {
        $records = [];
        $totalArrival = 0;
        $totalDeparture = 0;

        foreach ($this->orderMovementMatrix($movementMatrix) as $period => $branchRows) {
            foreach ($branchRows as $branchId => $counts) {
                $branchId = (int) $branchId;
                $arrival = (int) ($counts['arrival'] ?? 0);
                $departure = (int) ($counts['departure'] ?? 0);
                $movementTime = $this->normaliseTime($counts['movement_time'] ?? null);
                $arrivalTime = $this->normaliseTime($counts['arrival_time'] ?? null) ?: $movementTime;
                $departureTime = $this->normaliseTime($counts['departure_time'] ?? null) ?: $movementTime;

                $records[] = [
                    'branch_id' => $branchId,
                    'movement_period' => $period,
                    'movement_type' => 'arrival',
                    'headcount' => $arrival,
                    'movement_time' => $arrivalTime,
                ];
                $records[] = [
                    'branch_id' => $branchId,
                    'movement_period' => $period,
                    'movement_type' => 'departure',
                    'headcount' => $departure,
                    'movement_time' => $departureTime,
                ];

                $totalArrival += $arrival;
                $totalDeparture += $departure;
            }
        }

        $trip->update([
            'arrival_count' => $totalArrival,
            'departure_count' => $totalDeparture,
            'arrival_time' => $this->resolveBoundaryTime($movementMatrix, 'arrival_time', 'min', $trip->shift),
            'departure_time' => $this->resolveBoundaryTime($movementMatrix, 'departure_time', 'max', $trip->shift),
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

        return ! empty($visibleBranchIds);
    }

    private function resolveContextBranchId(ShuttleTrip $trip, array $visibleBranchIds, ?int $preferredBranchId = null): int
    {
        if ($preferredBranchId !== null && in_array($preferredBranchId, $visibleBranchIds, true)) {
            return $preferredBranchId;
        }

        if (in_array((int) $trip->branch_id, $visibleBranchIds, true)) {
            return (int) $trip->branch_id;
        }

        foreach ($visibleBranchIds as $branchId) {
            if (
                $trip->relationLoaded('branchMovements')
                && $trip->branchMovements->contains(fn ($movement) => (int) $movement->branch_id === (int) $branchId)
            ) {
                return (int) $branchId;
            }

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

        return $this->orderMovementMatrix(
            $trip->branchMovements
                ->groupBy(fn ($movement) => $this->normalisePeriod($movement->movement_period ?? null))
                ->map(function ($periodItems) {
                    return $periodItems
                        ->groupBy('branch_id')
                        ->map(function ($items) {
                            $arrivalMovement = $items->firstWhere('movement_type', 'arrival');
                            $departureMovement = $items->firstWhere('movement_type', 'departure');

                            return [
                                'arrival' => (int) optional($arrivalMovement)->headcount,
                                'departure' => (int) optional($departureMovement)->headcount,
                                'movement_time' => $this->normaliseTime(optional($arrivalMovement)->movement_time)
                                    ?: $this->normaliseTime(optional($departureMovement)->movement_time),
                                'arrival_time' => $this->normaliseTime(optional($arrivalMovement)->movement_time),
                                'departure_time' => $this->normaliseTime(optional($departureMovement)->movement_time),
                            ];
                        })
                        ->toArray();
                })
                ->toArray()
        );
    }

    private function completedTripCompletionsForContext(?int $currentBranchId, Carbon $listStartDate, Carbon $listEndDate)
    {
        if ($currentBranchId === null) {
            return collect();
        }

        $listQueryEndDate = $listEndDate->copy()->addDay();

        return ShuttleTripBranchCompletion::query()
            ->with('trip')
            ->where('branch_id', $currentBranchId)
            ->whereNotNull('completed_at')
            ->whereHas('trip', function ($query) use ($listStartDate, $listQueryEndDate) {
                $query->whereBetween('trip_date', [$listStartDate->toDateString(), $listQueryEndDate->toDateString()]);
            })
            ->get()
            ->filter(fn (ShuttleTripBranchCompletion $completion) => $completion->trip
                && $this->tripBelongsToOperationDateRange($completion->trip, $listStartDate, $listEndDate))
            ->keyBy('shuttle_trip_id');
    }

    private function filterTripsForOperationDateRange($trips, Carbon $startDate, Carbon $endDate)
    {
        return collect($trips)
            ->filter(fn (ShuttleTrip $trip) => $this->tripBelongsToOperationDateRange($trip, $startDate, $endDate))
            ->values();
    }

    private function annotateTripsWithOperationDate($trips)
    {
        return collect($trips)
            ->map(function (ShuttleTrip $trip) {
                $operationDate = $this->operationDateForTrip($trip);
                $trip->setAttribute('operation_date_for_list', $operationDate->toDateString());
                $trip->setAttribute('operation_date_display', $operationDate->format('d.m.Y'));

                return $trip;
            })
            ->values();
    }

    private function tripBelongsToOperationDateRange(ShuttleTrip $trip, Carbon $startDate, Carbon $endDate): bool
    {
        $operationDate = $this->operationDateForTrip($trip)->toDateString();

        return $operationDate >= $startDate->toDateString()
            && $operationDate <= $endDate->toDateString();
    }

    private function operationDateForTrip(ShuttleTrip $trip): Carbon
    {
        $tripDate = $trip->trip_date instanceof Carbon
            ? $trip->trip_date->copy()
            : Carbon::parse($trip->trip_date);

        $tripDate->startOfDay();

        if ($trip->is_lodging_trip) {
            $movementTime = $this->normaliseTime($trip->arrival_time)
                ?: $this->normaliseTime($trip->departure_time);

            return $this->timeBelongsToPreviousOperationDay($movementTime)
                ? $tripDate->subDay()
                : $tripDate;
        }

        return $this->isNightShift($trip->shift)
            ? $tripDate->subDay()
            : $tripDate;
    }

    private function operationDateForShiftDate(string $tripDate, ?string $shift): Carbon
    {
        $date = Carbon::parse($tripDate)->startOfDay();

        return $this->isNightShift($shift) ? $date->subDay() : $date;
    }

    private function actualTripDateForOperationDate(string $operationDate, ?string $shift): Carbon
    {
        $date = Carbon::parse($operationDate)->startOfDay();

        return $this->isNightShift($shift) ? $date->addDay() : $date;
    }

    private function operationDateForLodgingData(string $tripDate, ?string $movementTime): Carbon
    {
        $date = Carbon::parse($tripDate)->startOfDay();

        return $this->timeBelongsToPreviousOperationDay($movementTime) ? $date->subDay() : $date;
    }

    private function currentOperationDate(): Carbon
    {
        $now = Carbon::now();

        return $this->timeBelongsToPreviousOperationDay($now->format('H:i'))
            ? $now->copy()->subDay()->startOfDay()
            : $now->copy()->startOfDay();
    }

    private function defaultLodgingDateForOperationDate(Carbon $operationDate): Carbon
    {
        return $operationDate->isSameDay($this->currentOperationDate())
            ? Carbon::today()
            : $operationDate->copy();
    }

    private function timeBelongsToPreviousOperationDay(?string $time): bool
    {
        $time = $this->normaliseTime($time);

        return $time !== null && $time < self::OPERATION_DAY_START_TIME;
    }

    private function isNightShift(?string $shift): bool
    {
        return $this->shiftScheduleKey($shift) === 'C';
    }

    private function shiftScheduleKey(?string $shift): string
    {
        $value = strtoupper(trim((string) $shift));
        $value = str_replace(['Ä°', 'İ', 'ı'], 'I', $value);

        if (str_contains($value, 'LOJMAN')) {
            return 'LOJMAN';
        }

        if (str_contains($value, 'ARA')) {
            return 'ARA';
        }

        if (str_contains($value, 'IDARI') || str_contains($value, 'DARI')) {
            return 'IDARI';
        }

        if (str_starts_with($value, 'A')) {
            return 'A';
        }

        if (str_starts_with($value, 'B')) {
            return 'B';
        }

        if (str_starts_with($value, 'C')) {
            return 'C';
        }

        return 'OTHER';
    }

    private function shiftStartMinute(?string $shift): int
    {
        return match ($this->shiftScheduleKey($shift)) {
            'A' => 8 * 60,
            'IDARI' => 9 * 60,
            'ARA' => 13 * 60,
            'B' => 16 * 60,
            'C' => 24 * 60,
            'LOJMAN' => 25 * 60,
            default => 99 * 60,
        };
    }

    private function movementSortMinute(?string $time, ?string $shift): ?int
    {
        $minutes = $this->timeToMinutes($time);

        if ($minutes === null) {
            return null;
        }

        if (in_array($this->shiftScheduleKey($shift), ['B', 'C'], true) && $this->timeBelongsToPreviousOperationDay($time)) {
            return $minutes + (24 * 60);
        }

        return $minutes;
    }

    private function timeToMinutes(?string $time): ?int
    {
        $time = $this->normaliseTime($time);

        if ($time === null || ! str_contains($time, ':')) {
            return null;
        }

        [$hour, $minute] = array_map('intval', explode(':', $time, 2));

        return ($hour * 60) + $minute;
    }

    private function filterTripsForCompletion($trips, ?int $currentBranchId, bool $showCompleted, array $completedTripIds)
    {
        if ($currentBranchId === null) {
            return $trips->values();
        }

        if ($showCompleted) {
            return $trips
                ->filter(fn (ShuttleTrip $trip) => in_array((int) $trip->id, $completedTripIds, true))
                ->values();
        }

        if ($completedTripIds === []) {
            return $trips->values();
        }

        return $trips
            ->reject(fn (ShuttleTrip $trip) => in_array((int) $trip->id, $completedTripIds, true))
            ->values();
    }

    private function tripHasMissingCompletionData(ShuttleTrip $trip, int $branchId): bool
    {
        [$arrival, $departure] = $this->tripCompletionCountsForBranch($trip, $branchId);

        if ($trip->is_lodging_trip) {
            return $arrival <= 0 && $departure <= 0;
        }

        return $arrival <= 0 || $departure <= 0;
    }

    private function tripCompletionCountsForBranch(ShuttleTrip $trip, int $branchId): array
    {
        $trip->loadMissing('branchMovements');

        if ($trip->branchMovements->isEmpty() && (int) $trip->branch_id === $branchId) {
            return [(int) $trip->arrival_count, (int) $trip->departure_count];
        }

        $branchMovements = $trip->branchMovements
            ->filter(fn ($movement) => (int) $movement->branch_id === $branchId);

        return [
            (int) $branchMovements
                ->filter(fn ($movement) => $movement->movement_type === 'arrival')
                ->sum('headcount'),
            (int) $branchMovements
                ->filter(fn ($movement) => $movement->movement_type === 'departure')
                ->sum('headcount'),
        ];
    }

    private function summariseTripsForContext($trips, ?int $currentBranchId, array $visibleBranchIds): array
    {
        $incoming = 0;
        $outgoing = 0;
        $lodgingIncoming = 0;
        $lodgingOutgoing = 0;
        $lodgingTrips = 0;
        $contextBranchIds = $this->contextBranchIds($currentBranchId, $visibleBranchIds);

        foreach ($trips as $trip) {
            if ($trip->branchMovements->isEmpty()) {
                if (! in_array((int) $trip->branch_id, $contextBranchIds, true)) {
                    continue;
                }

                if ($trip->is_lodging_trip) {
                    $arrival = (int) $trip->arrival_count;
                    $departure = (int) $trip->departure_count;
                    $lodgingIncoming += $arrival;
                    $lodgingOutgoing += $departure;
                    $lodgingTrips += ($arrival > 0 ? 1 : 0) + ($departure > 0 ? 1 : 0);

                    continue;
                }

                $incoming += (int) $trip->arrival_count;
                $outgoing += (int) $trip->departure_count;

                continue;
            }

            if ($trip->is_lodging_trip) {
                foreach ($contextBranchIds as $branchId) {
                    $arrivalMovements = $trip->branchMovements
                        ->filter(fn ($movement) => (int) $movement->branch_id === $branchId
                            && $movement->movement_type === 'arrival'
                            && (int) $movement->headcount > 0);
                    $departureMovements = $trip->branchMovements
                        ->filter(fn ($movement) => (int) $movement->branch_id === $branchId
                            && $movement->movement_type === 'departure'
                            && (int) $movement->headcount > 0);

                    $lodgingIncoming += (int) $arrivalMovements->sum('headcount');
                    $lodgingOutgoing += (int) $departureMovements->sum('headcount');
                    $lodgingTrips += $arrivalMovements->count() + $departureMovements->count();
                }

                continue;
            }

            foreach ($contextBranchIds as $branchId) {
                $incoming += (int) $trip->branchMovements
                    ->filter(fn ($movement) => (int) $movement->branch_id === $branchId && $movement->movement_type === 'arrival')
                    ->sum('headcount');
                $outgoing += (int) $trip->branchMovements
                    ->filter(fn ($movement) => (int) $movement->branch_id === $branchId && $movement->movement_type === 'departure')
                    ->sum('headcount');
            }
        }

        return [$incoming, $outgoing, $lodgingIncoming, $lodgingOutgoing, $lodgingTrips];
    }

    private function countServiceTripsForContext($trips, ?int $currentBranchId, array $visibleBranchIds): int
    {
        $contextBranchIds = $this->contextBranchIds($currentBranchId, $visibleBranchIds);

        return (int) collect($trips)
            ->filter(fn (ShuttleTrip $trip) => ! $trip->is_lodging_trip
                && $this->tripMergeService->tripTouchesBranches($trip, $contextBranchIds))
            ->count();
    }

    private function contextBranchIds(?int $currentBranchId, array $visibleBranchIds): array
    {
        return $currentBranchId !== null
            ? [(int) $currentBranchId]
            : array_map('intval', $visibleBranchIds);
    }

    private function sortTripsForOperation($trips, ?int $currentBranchId)
    {
        if ($currentBranchId === null) {
            return $trips->values();
        }

        return $trips->sort(function (ShuttleTrip $first, ShuttleTrip $second) use ($currentBranchId) {
            $firstDate = $this->operationDateForTrip($first)->toDateString();
            $secondDate = $this->operationDateForTrip($second)->toDateString();

            if ($firstDate !== $secondDate) {
                return strcmp($firstDate, $secondDate);
            }

            $firstComplete = $this->tripBranchMovementsComplete($first, $currentBranchId);
            $secondComplete = $this->tripBranchMovementsComplete($second, $currentBranchId);

            if ($firstComplete !== $secondComplete) {
                return (int) $firstComplete <=> (int) $secondComplete;
            }

            $firstShiftOrder = $this->shiftStartMinute($first->shift);
            $secondShiftOrder = $this->shiftStartMinute($second->shift);

            if ($firstShiftOrder !== $secondShiftOrder) {
                return $firstShiftOrder <=> $secondShiftOrder;
            }

            $firstTime = $first->arrival_time ?: $first->departure_time ?: '99:99';
            $secondTime = $second->arrival_time ?: $second->departure_time ?: '99:99';
            $firstTimeOrder = $this->movementSortMinute($firstTime, $first->shift) ?? (99 * 60);
            $secondTimeOrder = $this->movementSortMinute($secondTime, $second->shift) ?? (99 * 60);

            if ($firstTimeOrder !== $secondTimeOrder) {
                return $firstTimeOrder <=> $secondTimeOrder;
            }

            return (int) $first->id <=> (int) $second->id;
        })->values();
    }

    private function tripBranchMovementsComplete(ShuttleTrip $trip, int $branchId): bool
    {
        $movementMatrix = $this->branchMovementMatrix($trip);

        foreach ($this->movementPeriods() as $period) {
            $movementRow = (array) data_get($movementMatrix, "{$period}.{$branchId}", $this->emptyMovementRow());

            if (! $this->movementRowHasData($movementRow)) {
                return false;
            }
        }

        return true;
    }

    private function normaliseTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return substr(trim($value), 0, 5);
    }

    private function normaliseMovementTime(array $row): ?string
    {
        return $this->normaliseTime($row['movement_time'] ?? null)
            ?: $this->normaliseTime($row['arrival_time'] ?? null)
            ?: $this->normaliseTime($row['departure_time'] ?? null);
    }

    private function normalisePeriod(?string $value): string
    {
        return array_key_exists((string) $value, ShuttleTripBranchMovement::PERIODS)
            ? (string) $value
            : ShuttleTripBranchMovement::DEFAULT_PERIOD;
    }

    private function movementPeriods(): array
    {
        return array_keys(ShuttleTripBranchMovement::PERIODS);
    }

    private function serviceShifts(): array
    {
        return array_values(array_filter(
            ShuttleTrip::SHIFTS,
            fn (string $shift) => strcasecmp(trim($shift), 'Lojman') !== 0
        ));
    }

    private function emptyMovementRow(): array
    {
        return [
            'arrival' => 0,
            'departure' => 0,
            'movement_time' => null,
            'arrival_time' => null,
            'departure_time' => null,
        ];
    }

    private function movementRowHasData(array $row): bool
    {
        return (int) ($row['arrival'] ?? 0) > 0
            || (int) ($row['departure'] ?? 0) > 0
            || ! empty($row['movement_time'])
            || ! empty($row['arrival_time'])
            || ! empty($row['departure_time']);
    }

    private function resolveBoundaryTime(array $movementMatrix, string $key, string $mode, ?string $shift = null): ?string
    {
        $times = collect($movementMatrix)
            ->flatMap(fn (array $periodRows) => collect($periodRows)->pluck($key))
            ->filter()
            ->map(fn ($time) => $this->normaliseTime($time))
            ->filter()
            ->map(fn (string $time) => [
                'time' => $time,
                'sort' => $this->movementSortMinute($time, $shift) ?? (99 * 60),
            ])
            ->sortBy('sort')
            ->values();

        if ($times->isEmpty()) {
            return null;
        }

        return $mode === 'min' ? $times->first()['time'] : $times->last()['time'];
    }

    private function mergeMovementMatrices(array $baseMatrix, array $overrideMatrix): array
    {
        $merged = $baseMatrix;

        foreach ($overrideMatrix as $period => $branchRows) {
            $period = $this->normalisePeriod($period);

            foreach ($branchRows as $branchId => $counts) {
                $movementTime = $this->normaliseMovementTime($counts);
                $normalisedCounts = [
                    'arrival' => (int) ($counts['arrival'] ?? 0),
                    'departure' => (int) ($counts['departure'] ?? 0),
                    'movement_time' => $movementTime,
                    'arrival_time' => $movementTime,
                    'departure_time' => $movementTime,
                ];

                if (
                    ! $this->movementRowHasData($normalisedCounts)
                    && isset($merged[$period][(int) $branchId])
                    && $this->movementRowHasData($merged[$period][(int) $branchId])
                ) {
                    continue;
                }

                $merged[$period][(int) $branchId] = $normalisedCounts;
            }
        }

        return $this->orderMovementMatrix($merged);
    }

    private function orderMovementMatrix(array $movementMatrix): array
    {
        $ordered = [];

        foreach ($this->movementPeriods() as $period) {
            if (! array_key_exists($period, $movementMatrix)) {
                continue;
            }

            ksort($movementMatrix[$period]);
            $ordered[$period] = $movementMatrix[$period];
        }

        foreach ($movementMatrix as $period => $branchRows) {
            $period = $this->normalisePeriod($period);

            if (array_key_exists($period, $ordered)) {
                continue;
            }

            ksort($branchRows);
            $ordered[$period] = $branchRows;
        }

        return $ordered;
    }

    private function mergeNotes(?string $existing, ?string $incoming): ?string
    {
        $existing = $existing ? trim($existing) : null;
        $incoming = $incoming ? trim($incoming) : null;

        if (! $existing) {
            return $incoming;
        }

        if (! $incoming || $incoming === $existing) {
            return $existing;
        }

        return collect([$existing, $incoming])
            ->filter()
            ->unique()
            ->implode(' | ');
    }
}
