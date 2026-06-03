<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\ShuttleTrip;
use App\Models\ShuttleTripBranchMovement;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class ShuttleTripMergeService
{
    public function mergeCollection($trips): Collection
    {
        return collect($trips)
            ->filter()
            ->groupBy(fn (ShuttleTrip $trip) => $this->groupKey($trip))
            ->map(fn (Collection $group) => $this->mergeGroupForDisplay($group))
            ->sortBy(fn (ShuttleTrip $trip) => $this->sortKey($trip))
            ->values();
    }

    public function buildDisplayTrip(ShuttleTrip $trip): ShuttleTrip
    {
        return $this->mergeGroupForDisplay($this->loadGroup($trip));
    }

    public function consolidateTrip(ShuttleTrip $trip): ShuttleTrip
    {
        $group = $this->loadGroup($trip);
        $primary = $group->sortBy('id')->first();

        if (! $primary) {
            return $trip->loadMissing(['vehicle', 'route', 'branch', 'creator', 'branchMovements.branch']);
        }

        if ($group->count() === 1) {
            return $primary->loadMissing(['vehicle', 'route', 'branch', 'creator', 'branchMovements.branch']);
        }

        $periodMatrix = $this->buildPeriodMatrix($group);
        $route = $group->first(fn (ShuttleTrip $candidate) => ! empty($candidate->route_id))?->route;

        $primary->update([
            'route_id' => $route?->id,
            'arrival_time' => $this->resolveBoundaryTime($periodMatrix, 'arrival_time', 'min'),
            'arrival_count' => $this->sumPeriodMatrix($periodMatrix, 'arrival'),
            'departure_time' => $this->resolveBoundaryTime($periodMatrix, 'departure_time', 'max'),
            'departure_count' => $this->sumPeriodMatrix($periodMatrix, 'departure'),
            'arrived_with_different_vehicle' => $group->contains(fn (ShuttleTrip $candidate) => (bool) $candidate->arrived_with_different_vehicle),
            'is_transfer' => $group->contains(fn (ShuttleTrip $candidate) => (bool) $candidate->is_transfer),
            'is_lodging_route' => $group->contains(fn (ShuttleTrip $candidate) => (bool) $candidate->is_lodging_route),
            'notes' => $this->mergeNoteList($group->pluck('notes')->all()),
        ]);

        $primary->branchMovements()->delete();
        $records = $this->serialisePeriodMatrix($periodMatrix);
        if ($records !== []) {
            $primary->branchMovements()->createMany($records);
        }

        $group
            ->filter(fn (ShuttleTrip $candidate) => (int) $candidate->id !== (int) $primary->id)
            ->each(function (ShuttleTrip $duplicate): void {
                $duplicate->branchMovements()->delete();
                $duplicate->delete();
            });

        return $primary->fresh(['vehicle', 'route', 'branch', 'creator', 'branchMovements.branch']);
    }

    public function tripTouchesBranches(ShuttleTrip $trip, array $branchIds): bool
    {
        $branchIds = array_map('intval', $branchIds);

        if ($branchIds === []) {
            return true;
        }

        if ($trip->branchMovements->isEmpty()) {
            return in_array((int) $trip->branch_id, $branchIds, true);
        }

        return $trip->branchMovements->contains(
            fn (ShuttleTripBranchMovement $movement) => in_array((int) $movement->branch_id, $branchIds, true)
        );
    }

    private function loadGroup(ShuttleTrip $trip): EloquentCollection
    {
        $tripDate = $trip->trip_date instanceof \Carbon\CarbonInterface
            ? $trip->trip_date->toDateString()
            : (string) $trip->trip_date;

        return ShuttleTrip::with(['vehicle', 'route', 'branch', 'creator', 'branchMovements.branch'])
            ->where('trip_date', $tripDate)
            ->where('shift', $trip->shift)
            ->where('shuttle_vehicle_id', $trip->shuttle_vehicle_id)
            ->orderBy('id')
            ->get();
    }

    private function mergeGroupForDisplay(Collection $group): ShuttleTrip
    {
        /** @var ShuttleTrip $primary */
        $primary = $group->sortBy('id')->first();
        $primary->loadMissing(['vehicle', 'route', 'branch', 'creator', 'branchMovements.branch']);

        $periodMatrix = $this->buildPeriodMatrix($group);
        $route = $group->first(fn (ShuttleTrip $candidate) => ! empty($candidate->route_id))?->route;

        $primary->route_id = $route?->id;
        if ($route) {
            $primary->setRelation('route', $route);
        }

        $primary->arrival_count = $this->sumPeriodMatrix($periodMatrix, 'arrival');
        $primary->departure_count = $this->sumPeriodMatrix($periodMatrix, 'departure');
        $primary->arrival_time = $this->resolveBoundaryTime($periodMatrix, 'arrival_time', 'min');
        $primary->departure_time = $this->resolveBoundaryTime($periodMatrix, 'departure_time', 'max');
        $primary->arrived_with_different_vehicle = $group->contains(
            fn (ShuttleTrip $candidate) => (bool) $candidate->arrived_with_different_vehicle
        );
        $primary->is_transfer = $group->contains(
            fn (ShuttleTrip $candidate) => (bool) $candidate->is_transfer
        );
        $primary->is_lodging_route = $group->contains(
            fn (ShuttleTrip $candidate) => (bool) $candidate->is_lodging_route
        );
        $primary->notes = $this->mergeNoteList($group->pluck('notes')->all());
        $primary->setRelation('branchMovements', $this->hydratePeriodMovements($primary, $periodMatrix));

        return $primary;
    }

    private function buildPeriodMatrix(Collection $group): array
    {
        $matrix = [];

        foreach ($group as $trip) {
            $trip->loadMissing(['branch', 'branchMovements.branch']);

            if ($trip->branchMovements->isEmpty()) {
                $period = ShuttleTripBranchMovement::DEFAULT_PERIOD;
                $branchId = (int) $trip->branch_id;
                $this->primeBranchRow($matrix, $period, $branchId, $trip->branch);
                $this->applyMovementToRow($matrix[$period][$branchId], 'arrival', (int) $trip->arrival_count, $trip->arrival_time);
                $this->applyMovementToRow($matrix[$period][$branchId], 'departure', (int) $trip->departure_count, $trip->departure_time);
                continue;
            }

            foreach ($trip->branchMovements as $movement) {
                $period = $this->normalisePeriod($movement->movement_period ?? null);
                $branchId = (int) $movement->branch_id;
                $this->primeBranchRow($matrix, $period, $branchId, $movement->branch);
                $this->applyMovementToRow(
                    $matrix[$period][$branchId],
                    $movement->movement_type,
                    (int) $movement->headcount,
                    $movement->movement_time
                );
            }
        }

        return $this->orderPeriodMatrix($matrix);
    }

    private function hydratePeriodMovements(ShuttleTrip $trip, array $periodMatrix): EloquentCollection
    {
        $movements = new EloquentCollection();

        foreach ($this->orderPeriodMatrix($periodMatrix) as $period => $branchRows) {
            foreach ($branchRows as $branchId => $row) {
                foreach (['arrival', 'departure'] as $movementType) {
                    $movement = new ShuttleTripBranchMovement();
                    $movement->forceFill([
                        'shuttle_trip_id' => $trip->id,
                        'branch_id' => (int) $branchId,
                        'movement_period' => $period,
                        'movement_type' => $movementType,
                        'headcount' => (int) ($row[$movementType] ?? 0),
                        'movement_time' => $row[$movementType . '_time'] ?? null,
                    ]);
                    $movement->exists = true;

                    if (! empty($row['branch'])) {
                        $movement->setRelation('branch', $row['branch']);
                    }

                    $movements->push($movement);
                }
            }
        }

        return $movements;
    }

    private function serialisePeriodMatrix(array $periodMatrix): array
    {
        $records = [];

        foreach ($this->orderPeriodMatrix($periodMatrix) as $period => $branchRows) {
            foreach ($branchRows as $branchId => $row) {
                $records[] = [
                    'branch_id' => (int) $branchId,
                    'movement_period' => $period,
                    'movement_type' => 'arrival',
                    'headcount' => (int) ($row['arrival'] ?? 0),
                    'movement_time' => $row['arrival_time'] ?? null,
                ];
                $records[] = [
                    'branch_id' => (int) $branchId,
                    'movement_period' => $period,
                    'movement_type' => 'departure',
                    'headcount' => (int) ($row['departure'] ?? 0),
                    'movement_time' => $row['departure_time'] ?? null,
                ];
            }
        }

        return $records;
    }

    private function primeBranchRow(array &$matrix, string $period, int $branchId, ?Branch $branch): void
    {
        $period = $this->normalisePeriod($period);

        if (! array_key_exists($period, $matrix)) {
            $matrix[$period] = [];
        }

        if (! array_key_exists($branchId, $matrix[$period])) {
            $matrix[$period][$branchId] = [
                'branch' => $branch,
                'arrival' => 0,
                'departure' => 0,
                'arrival_time' => null,
                'departure_time' => null,
            ];

            return;
        }

        if (empty($matrix[$period][$branchId]['branch']) && $branch) {
            $matrix[$period][$branchId]['branch'] = $branch;
        }
    }

    private function applyMovementToRow(array &$row, string $movementType, int $count, ?string $time): void
    {
        $time = $this->normaliseTime($time);

        if ($movementType === 'arrival') {
            $row['arrival'] = max((int) $row['arrival'], $count);
            $row['arrival_time'] = $this->pickTime($row['arrival_time'], $time, 'min');

            return;
        }

        $row['departure'] = max((int) $row['departure'], $count);
        $row['departure_time'] = $this->pickTime($row['departure_time'], $time, 'max');
    }

    private function sumPeriodMatrix(array $periodMatrix, string $column): int
    {
        return (int) collect($periodMatrix)->sum(
            fn (array $branchRows) => collect($branchRows)->sum(fn (array $row) => (int) ($row[$column] ?? 0))
        );
    }

    private function resolveBoundaryTime(array $periodMatrix, string $column, string $mode): ?string
    {
        $times = collect($periodMatrix)
            ->flatMap(fn (array $branchRows) => collect($branchRows)->pluck($column))
            ->filter()
            ->map(fn (?string $time) => $this->normaliseTime($time))
            ->filter()
            ->sort()
            ->values();

        if ($times->isEmpty()) {
            return null;
        }

        return $mode === 'min' ? $times->first() : $times->last();
    }

    private function pickTime(?string $current, ?string $candidate, string $mode): ?string
    {
        $current = $this->normaliseTime($current);
        $candidate = $this->normaliseTime($candidate);

        if (! $candidate) {
            return $current;
        }

        if (! $current) {
            return $candidate;
        }

        return $mode === 'min'
            ? min($current, $candidate)
            : max($current, $candidate);
    }

    private function mergeNoteList(array $notes): ?string
    {
        $notes = collect($notes)
            ->filter(fn ($note) => filled($note))
            ->map(fn ($note) => trim((string) $note))
            ->filter()
            ->unique()
            ->values();

        return $notes->isEmpty() ? null : $notes->implode(' | ');
    }

    private function normalisePeriod(?string $value): string
    {
        return array_key_exists((string) $value, ShuttleTripBranchMovement::PERIODS)
            ? (string) $value
            : ShuttleTripBranchMovement::DEFAULT_PERIOD;
    }

    private function normaliseTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return substr(trim($value), 0, 5);
    }

    private function orderPeriodMatrix(array $periodMatrix): array
    {
        $ordered = [];

        foreach (array_keys(ShuttleTripBranchMovement::PERIODS) as $period) {
            if (! array_key_exists($period, $periodMatrix)) {
                continue;
            }

            ksort($periodMatrix[$period]);
            $ordered[$period] = $periodMatrix[$period];
        }

        foreach ($periodMatrix as $period => $branchRows) {
            if (array_key_exists($period, $ordered)) {
                continue;
            }

            ksort($branchRows);
            $ordered[$this->normalisePeriod($period)] = $branchRows;
        }

        return $ordered;
    }

    private function groupKey(ShuttleTrip $trip): string
    {
        $tripDate = $trip->trip_date instanceof \Carbon\CarbonInterface
            ? $trip->trip_date->toDateString()
            : (string) $trip->trip_date;

        return implode('|', [
            $tripDate,
            (string) $trip->shift,
            (string) $trip->shuttle_vehicle_id,
        ]);
    }

    private function sortKey(ShuttleTrip $trip): string
    {
        $tripDate = $trip->trip_date instanceof \Carbon\CarbonInterface
            ? $trip->trip_date->toDateString()
            : (string) $trip->trip_date;

        $shiftIndex = array_search($trip->shift, ShuttleTrip::SHIFTS, true);
        $shiftIndex = $shiftIndex === false ? 99 : $shiftIndex;

        return sprintf(
            '%s|%02d|%s|%s',
            $tripDate,
            $shiftIndex,
            $trip->arrival_time ?? '99:99',
            $trip->vehicle->plate ?? ''
        );
    }
}
