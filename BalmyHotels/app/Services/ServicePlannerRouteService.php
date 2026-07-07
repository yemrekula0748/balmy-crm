<?php

namespace App\Services;

use App\Models\ServicePlannerAssignment;
use App\Models\ServicePlannerPlan;
use App\Models\ServicePlannerStop;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServicePlannerRouteService
{
    public function __construct(
        private readonly ServicePlannerGeocodingService $geocodingService
    ) {
    }

    public function calculate(ServicePlannerPlan $plan): array
    {
        $plan->load(['vehicles', 'stops', 'vehicles.assignments']);

        if ($plan->vehicles->isEmpty()) {
            throw ValidationException::withMessages([
                'vehicles' => 'En az bir servis tanimi olmadan rota hesaplanamaz.',
            ]);
        }

        if ($plan->stops->isEmpty()) {
            throw ValidationException::withMessages([
                'excel_file' => 'Excel yuklenmeden rota hesaplanamaz.',
            ]);
        }

        $this->geocodePlanStart($plan);
        $stops = $this->geocodeStops($plan);
        $vehicles = $plan->vehicles->values();

        $totalCapacity = (int) $vehicles->sum('seat_capacity');
        if ($stops->count() > $totalCapacity) {
            throw ValidationException::withMessages([
                'capacity' => 'Toplam koltuk kapasitesi (' . $totalCapacity . ') yuklenen personel sayisini (' . $stops->count() . ') karsilamiyor.',
            ]);
        }

        $rawRoutes = $this->buildPlanAssignments(
            $vehicles->map(fn ($vehicle) => [
                'id' => $vehicle->id,
                'name' => $vehicle->name,
                'seat_capacity' => (int) $vehicle->seat_capacity,
                'vehicle_order' => (int) $vehicle->vehicle_order,
            ])->all(),
            $stops->map(fn ($stop) => [
                'id' => $stop->id,
                'name' => $stop->passenger_name,
                'address' => $stop->address,
                'latitude' => (float) $stop->latitude,
                'longitude' => (float) $stop->longitude,
            ])->all(),
            [
                'latitude' => (float) $plan->start_latitude,
                'longitude' => (float) $plan->start_longitude,
            ]
        );

        DB::transaction(function () use ($plan, $rawRoutes) {
            ServicePlannerAssignment::query()
                ->whereIn('service_planner_vehicle_id', $plan->vehicles->pluck('id'))
                ->delete();

            foreach ($rawRoutes as $route) {
                $cumulativeDistance = 0.0;

                foreach ($route['stops'] as $index => $stopPayload) {
                    $cumulativeDistance += $stopPayload['leg_distance_km'];

                    ServicePlannerAssignment::create([
                        'service_planner_vehicle_id' => $route['vehicle']['id'],
                        'service_planner_stop_id' => $stopPayload['id'],
                        'stop_order' => $index + 1,
                        'leg_distance_km' => round($stopPayload['leg_distance_km'], 2),
                        'cumulative_distance_km' => round($cumulativeDistance, 2),
                        'travel_minutes' => $stopPayload['travel_minutes'],
                    ]);
                }
            }

            $plan->update([
                'status' => 'planned',
                'route_generated_at' => now(),
            ]);
        });

        return $rawRoutes;
    }

    public function buildPlanAssignments(array $vehicles, array $stops, array $start): array
    {
        if (empty($vehicles)) {
            return [];
        }

        usort($vehicles, fn ($left, $right) => ($left['vehicle_order'] ?? 0) <=> ($right['vehicle_order'] ?? 0));

        $preparedStops = collect($stops)
            ->map(function (array $stop) use ($start) {
                $stop['distance_to_start'] = $this->distanceKm($start, $stop);
                $stop['angle'] = $this->angleFromStart($start, $stop);

                return $stop;
            })
            ->sortBy([
                ['angle', 'asc'],
                ['distance_to_start', 'desc'],
            ])
            ->values()
            ->all();

        $bestPlan = null;
        $bestCost = null;
        $stopCount = count($preparedStops);

        for ($offset = 0; $offset < max(1, $stopCount); $offset++) {
            $rotatedStops = $this->rotateStops($preparedStops, $offset);
            $routes = [];
            $cursor = 0;

            foreach ($vehicles as $vehicle) {
                $capacity = max(0, (int) ($vehicle['seat_capacity'] ?? 0));
                $slice = array_slice($rotatedStops, $cursor, $capacity);
                $cursor += count($slice);
                $orderedSlice = $this->orderStopsByNearestNeighbor($slice, $start);

                $routes[] = [
                    'vehicle' => $vehicle,
                    'stops' => $orderedSlice,
                ];
            }

            if ($cursor < $stopCount) {
                continue;
            }

            $totalCost = 0.0;
            foreach ($routes as &$route) {
                $routeMetrics = $this->appendRouteMetrics($route['stops'], $start);
                $route['stops'] = $routeMetrics['stops'];
                $route['total_distance_km'] = $routeMetrics['total_distance_km'];
                $route['estimated_minutes'] = $routeMetrics['estimated_minutes'];
                $totalCost += $routeMetrics['total_distance_km'];
            }
            unset($route);

            if ($bestCost === null || $totalCost < $bestCost) {
                $bestCost = $totalCost;
                $bestPlan = $routes;
            }
        }

        return $bestPlan ?? [];
    }

    private function geocodePlanStart(ServicePlannerPlan $plan): void
    {
        if ($plan->start_latitude !== null && $plan->start_longitude !== null) {
            return;
        }

        $result = $this->geocodingService->geocode($plan->start_address);
        if (! $result) {
            throw ValidationException::withMessages([
                'start_address' => 'Kalkis noktasi cozumlenemedi. Lutfen adresi daha acik girin.',
            ]);
        }

        $plan->update([
            'start_latitude' => $result['latitude'],
            'start_longitude' => $result['longitude'],
            'geocoding_provider' => $result['provider'],
        ]);
    }

    private function geocodeStops(ServicePlannerPlan $plan): Collection
    {
        $failedStops = [];

        $stops = $plan->stops()->orderBy('row_number')->get();
        foreach ($stops as $stop) {
            if ($stop->latitude !== null && $stop->longitude !== null) {
                $stop->distance_to_start_km = round($this->distanceKm([
                    'latitude' => (float) $plan->start_latitude,
                    'longitude' => (float) $plan->start_longitude,
                ], [
                    'latitude' => (float) $stop->latitude,
                    'longitude' => (float) $stop->longitude,
                ]), 2);
                $stop->geocode_status = 'success';
                $stop->save();
                continue;
            }

            $queryAddress = trim($stop->address . ' ' . ($stop->district ? ' ' . $stop->district : ''));
            $result = $this->geocodingService->geocode($queryAddress);

            if (! $result) {
                $stop->update([
                    'geocode_status' => 'failed',
                    'geocode_message' => 'Adres cozumlenemedi',
                ]);

                $failedStops[] = $stop->passenger_name . ' - ' . $stop->address;
                continue;
            }

            $stop->update([
                'latitude' => $result['latitude'],
                'longitude' => $result['longitude'],
                'distance_to_start_km' => round($this->distanceKm([
                    'latitude' => (float) $plan->start_latitude,
                    'longitude' => (float) $plan->start_longitude,
                ], [
                    'latitude' => (float) $result['latitude'],
                    'longitude' => (float) $result['longitude'],
                ]), 2),
                'geocode_status' => 'success',
                'geocode_provider' => $result['provider'],
                'geocode_message' => $result['formatted_address'] ?? null,
            ]);
        }

        if (! empty($failedStops)) {
            throw ValidationException::withMessages([
                'excel_file' => 'Bazi adresler cozumlenemedi: ' . implode(' | ', array_slice($failedStops, 0, 8)),
            ]);
        }

        return $plan->stops()->orderBy('row_number')->get();
    }

    private function rotateStops(array $stops, int $offset): array
    {
        if ($offset <= 0 || empty($stops)) {
            return $stops;
        }

        return array_merge(
            array_slice($stops, $offset),
            array_slice($stops, 0, $offset)
        );
    }

    private function orderStopsByNearestNeighbor(array $stops, array $start): array
    {
        $remaining = array_values($stops);
        $ordered = [];
        $current = $start;

        while (! empty($remaining)) {
            $nearestIndex = 0;
            $nearestDistance = null;

            foreach ($remaining as $index => $stop) {
                $distance = $this->distanceKm($current, $stop);

                if ($nearestDistance === null || $distance < $nearestDistance) {
                    $nearestDistance = $distance;
                    $nearestIndex = $index;
                }
            }

            $ordered[] = $remaining[$nearestIndex];
            $current = $remaining[$nearestIndex];
            array_splice($remaining, $nearestIndex, 1);
        }

        return $ordered;
    }

    private function appendRouteMetrics(array $stops, array $start): array
    {
        $current = $start;
        $distanceTotal = 0.0;

        foreach ($stops as $index => $stop) {
            $distance = $this->distanceKm($current, $stop);
            $distanceTotal += $distance;
            $stops[$index]['leg_distance_km'] = round($distance, 2);
            $stops[$index]['travel_minutes'] = (int) max(3, round(($distance / 35) * 60));
            $current = $stop;
        }

        return [
            'stops' => $stops,
            'total_distance_km' => round($distanceTotal, 2),
            'estimated_minutes' => (int) round(($distanceTotal / 35) * 60),
        ];
    }

    private function angleFromStart(array $start, array $stop): float
    {
        return atan2(
            $stop['longitude'] - $start['longitude'],
            $stop['latitude'] - $start['latitude']
        );
    }

    private function distanceKm(array $from, array $to): float
    {
        $earthRadius = 6371;

        $latFrom = deg2rad((float) $from['latitude']);
        $lngFrom = deg2rad((float) $from['longitude']);
        $latTo = deg2rad((float) $to['latitude']);
        $lngTo = deg2rad((float) $to['longitude']);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2)
            + cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return $earthRadius * $angle * 1.18;
    }
}
