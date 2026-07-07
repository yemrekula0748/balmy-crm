<?php

namespace Tests\Unit;

use App\Services\ServicePlannerGeocodingService;
use App\Services\ServicePlannerRouteService;
use PHPUnit\Framework\TestCase;

class ServicePlannerRouteServiceTest extends TestCase
{
    public function test_it_assigns_all_stops_without_exceeding_capacity(): void
    {
        $service = new ServicePlannerRouteService(new ServicePlannerGeocodingService());

        $routes = $service->buildPlanAssignments(
            [
                ['id' => 1, 'name' => 'Servis 1', 'seat_capacity' => 2, 'vehicle_order' => 1],
                ['id' => 2, 'name' => 'Servis 2', 'seat_capacity' => 2, 'vehicle_order' => 2],
            ],
            [
                ['id' => 10, 'name' => 'A', 'address' => 'A', 'latitude' => 36.72, 'longitude' => 30.58],
                ['id' => 11, 'name' => 'B', 'address' => 'B', 'latitude' => 36.73, 'longitude' => 30.59],
                ['id' => 12, 'name' => 'C', 'address' => 'C', 'latitude' => 36.68, 'longitude' => 30.50],
                ['id' => 13, 'name' => 'D', 'address' => 'D', 'latitude' => 36.67, 'longitude' => 30.49],
            ],
            ['latitude' => 36.71, 'longitude' => 30.56]
        );

        $this->assertCount(2, $routes);

        $assignedStopIds = [];
        foreach ($routes as $route) {
            $this->assertLessThanOrEqual($route['vehicle']['seat_capacity'], count($route['stops']));

            foreach ($route['stops'] as $stop) {
                $assignedStopIds[] = $stop['id'];
                $this->assertArrayHasKey('leg_distance_km', $stop);
                $this->assertArrayHasKey('travel_minutes', $stop);
                $this->assertGreaterThanOrEqual(0, $stop['leg_distance_km']);
                $this->assertGreaterThan(0, $stop['travel_minutes']);
            }
        }

        sort($assignedStopIds);

        $this->assertSame([10, 11, 12, 13], $assignedStopIds);
    }
}
