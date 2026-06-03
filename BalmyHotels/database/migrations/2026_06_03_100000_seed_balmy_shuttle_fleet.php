<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $vehicles = [
            ['plate' => '07 C 0332', 'name' => 'Duray Metin', 'route' => 'LARA', 'type' => 'midibus', 'capacity' => 27],
            ['plate' => '07 C 1843', 'name' => 'Sabri Bahadır', 'route' => 'ŞELALE', 'type' => 'midibus', 'capacity' => 27],
            ['plate' => '07 C 1844', 'name' => 'Mustafa Kökçü', 'route' => 'VARSAK', 'type' => 'midibus', 'capacity' => 27],
            ['plate' => '07 C 3505', 'name' => 'Gevher Demir', 'route' => 'DOKUMA', 'type' => 'midibus', 'capacity' => 27],
            ['plate' => '07 C 0150', 'name' => 'Ercan Tahsin', 'route' => 'ZEYTİNKÖY', 'type' => 'midibus', 'capacity' => 27],
            ['plate' => '07 C 2751', 'name' => 'Osman Çelik', 'route' => 'TEOMANPASA', 'type' => 'minibus', 'capacity' => 16],
            ['plate' => '07 C 1295', 'name' => 'Hasan Deniz Özen', 'route' => 'DOKUMA EK', 'type' => 'minibus', 'capacity' => 16],
            ['plate' => '07 C 1633', 'name' => 'Nurullah Damar', 'route' => 'HURMA', 'type' => 'minibus', 'capacity' => 16],
            ['plate' => '07 C 0887', 'name' => 'Nihat Çağlayan', 'route' => 'ALTINOVA', 'type' => 'minibus', 'capacity' => 16],
            ['plate' => '07 C 1297', 'name' => 'Ali İhsan', 'route' => 'ÇARŞI İDARİ', 'type' => 'minibus', 'capacity' => 16],
            ['plate' => '07 C 1631', 'name' => 'Ali Bozan', 'route' => 'KEPEZ İDARİ', 'type' => 'minibus', 'capacity' => 16],
        ];

        foreach ($vehicles as $vehicle) {
            $routeId = DB::table('shuttle_routes')
                ->where('name', $vehicle['route'])
                ->value('id');

            if ($routeId) {
                DB::table('shuttle_routes')
                    ->where('id', $routeId)
                    ->update([
                        'branch_id' => null,
                        'description' => null,
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);
            } else {
                $routeId = DB::table('shuttle_routes')->insertGetId([
                    'branch_id' => null,
                    'description' => null,
                    'name' => $vehicle['route'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $vehicleId = DB::table('shuttle_vehicles')
                ->where('plate', $vehicle['plate'])
                ->value('id');

            $vehiclePayload = [
                'branch_id' => null,
                'name' => $vehicle['name'],
                'plate' => $vehicle['plate'],
                'type' => $vehicle['type'],
                'capacity' => $vehicle['capacity'],
                'is_active' => true,
                'updated_at' => $now,
            ];

            if ($vehicleId) {
                DB::table('shuttle_vehicles')
                    ->where('id', $vehicleId)
                    ->update($vehiclePayload);
            } else {
                $vehiclePayload['created_at'] = $now;
                $vehicleId = DB::table('shuttle_vehicles')->insertGetId($vehiclePayload);
            }

            if ($routeId) {
                DB::table('shuttle_route_vehicle')->updateOrInsert(
                    [
                        'shuttle_vehicle_id' => $vehicleId,
                        'shuttle_route_id' => $routeId,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        $plates = [
            '07 C 0332',
            '07 C 1843',
            '07 C 1844',
            '07 C 3505',
            '07 C 0150',
            '07 C 2751',
            '07 C 1295',
            '07 C 1633',
            '07 C 0887',
            '07 C 1297',
            '07 C 1631',
        ];

        $vehicleIds = DB::table('shuttle_vehicles')
            ->whereIn('plate', $plates)
            ->pluck('id');

        if ($vehicleIds->isEmpty()) {
            return;
        }

        DB::table('shuttle_route_vehicle')
            ->whereIn('shuttle_vehicle_id', $vehicleIds)
            ->delete();

        DB::table('shuttle_vehicles')
            ->whereIn('id', $vehicleIds)
            ->delete();
    }
};
