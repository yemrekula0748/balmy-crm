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

        $branchMatrix = $this->buildBranchMatrix($group);
        $route = $group->first(fn (ShuttleTrip $candidate) => ! empty($candidate->route_id))?->route;

        $primary->update([
            'route_id' => $route?->id,
            'arrival_time' => $this->resolveBoundaryTime($branchMatrix, 'arrival_time', 'min'),
            'arrival_count' => $this->sumBranchMatrix($branchMatrix, 'arrival'),
            'departure_time' => $this->resolveBoundaryTime($branchMatrix, 'departure_time', 'max'),
            'departure_count' => $this->sumBranchMatrix($branchMatrix, 'departure'),
            'arrived_with_different_vehicle' => $group->contains(fn (ShuttleTrip $candidate) => (bool) $candidate->arrived_with_different_vehicle),
            'is_transfer' => $group->contains(fn (ShuttleTrip $candidate) => (bool) $candidate->is_transfer),
            'notes' => $this->mergeNoteList($group->pluck('notes')->all()),
        ]);

        $primary->branchMovements()->delete();
        $records = $this->serialiseBranchMatrix($branchMatrix);
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

        $branchMatrix = $this->buildBranchMatrix($group);
        $route = $group->first(fn (ShuttleTrip $candidate) => ! empty($candidate->route_id))?->route;

        $primary->route_id = $route?->id;
        if ($route) {
            $primary->setRelation('route', $route);
        }

        $primary->arrival_count = $this->sumBranchMatrix($branchMatrix, 'arrival');
        $primary->departure_count = $this->sumBranchMatrix($branchMatrix, 'departure');
        $primary->arrival_time = $this->resolveBoundaryTime($branchMatrix, 'arrival_time', 'min');
        $primary->departure_time = $this->resolveBoundaryTime($branchMatrix, 'departure_time', 'max');
        $primary->arrived_with_different_vehicle = $group->contains(
            fn (ShuttleTrip $candidate) => (bool) $candidate->arrived_with_different_vehicle
        );
        $primary->is_transfer = $group->contains(
            fn (ShuttleTrip $candidate) => (bool) $candidate->is_transfer
        );
        $primary->notes = $this->mergeNoteList($group->pluck('notes')->all());
        $primary->setRelation('branchMovements', $this->hydrateBranchMovements($primary, $branchMatrix));

        return $primary;
    }

    private function buildBranchMatrix(Collection $group): array
    {
        $matrix = [];

        foreach ($group as $trip) {
            $trip->loadMissing(['branch', 'branchMovements.branch']);

            if ($trip->branchMovements->isEmpty()) {
                $branchId = (int) $trip->branch_id;
                $this->primeBranchRow($matrix, $branchId, $trip->branch);
                $this->applyMovementToMatrix($matrix[$branchId], 'arrival', (int) $trip->arrival_count, $trip->arrival_time);
                $this->applyMovementToMatrix($matrix[$branchId], 'departure', (int) $trip->departure_count, $trip->departure_time);
                continue;
            }

            foreach ($trip->branchMovements as $movement) {
                $branchId = (int) $movement->branch_id;
                $this->primeBranchRow($matrix, $branchId, $movement->branch);
                $this->applyMovementToMatrix(
                    $matrix[$branchId],
                    $movement->movement_type,
                    (int) $movement->headcount,
                    $movement->movement_time
                );
            }
        }

        ksort($matrix);

        return $matrix;
    }

    private function hydrateBranchMovements(ShuttleTrip $trip, array $branchMatrix): EloquentCollection
    {
        $movements = new EloquentCollection();

        foreach ($branchMatrix as $branchId => $row) {
            foreach (['arrival', 'departure'] as $movementType) {
                $movement = new ShuttleTripBranchMovement();
                $movement->forceFill([
                    'shuttle_trip_id' => $trip->id,
                    'branch_id' => (int) $branchId,
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

        return $movements;
    }

    private function serialiseBranchMatrix(array $branchMatrix): array
    {
        $records = [];

        foreach ($branchMatrix as $branchId => $row) {
            $records[] = [
                'branch_id' => (int) $branchId,
                'movement_type' => 'arrival',
                'headcount' => (int) ($row['arrival'] ?? 0),
                'movement_time' => $row['arrival_time'] ?? null,
            ];
            $records[] = [
                'branch_id' => (int) $branchId,
                'movement_type' => 'departure',
                'headcount' => (int) ($row['departure'] ?? 0),
                'movement_time' => $row['departure_time'] ?? null,
            ];
        }

        return $records;
    }

    private function primeBranchRow(array &$matrix, int $branchId, ?Branch $branch): void
    {
        if (! array_key_exists($branchId, $matrix)) {
            $matrix[$branchId] = [
                'branch' => $branch,
                'arrival' => 0,
                'departure' => 0,
                'arrival_time' => null,
                'departure_time' => null,
            ];

            return;
        }

        if (empty($matrix[$branchId]['branch']) && $branch) {
            $matrix[$branchId]['branch'] = $branch;
        }
    }

    private function applyMovementToMatrix(array &$row, string $movementType, int $count, ?string $time): void
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

    private function sumBranchMatrix(array $branchMatrix, string $column): int
    {
        return (int) collect($branchMatrix)->sum(fn (array $row) => (int) ($row[$column] ?? 0));
    }

    private function resolveBoundaryTime(array $branchMatrix, string $column, string $mode): ?string
    {
        $times = collect($branchMatrix)
            ->pluck($column)
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

    private function normaliseTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return substr(trim($value), 0, 5);
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
