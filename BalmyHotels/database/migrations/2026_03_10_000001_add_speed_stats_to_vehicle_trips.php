<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Konum kaydına anlık hız ekle (m/s'den dönüştürülmüş km/h)
        Schema::table('vehicle_trip_locations', function (Blueprint $table) {
            $table->decimal('speed', 5, 2)->nullable()->after('lng')->comment('km/h');
        });

        // Göreve GPS istatistikleri ekle
        Schema::table('vehicle_trips', function (Blueprint $table) {
            $table->decimal('gps_km', 8, 2)->nullable()->after('end_km')->comment('GPS ile hesaplanan km');
            $table->decimal('avg_speed', 5, 2)->nullable()->after('gps_km')->comment('Ortalama hız (km/h)');
            $table->decimal('min_speed', 5, 2)->nullable()->after('avg_speed')->comment('Minimum hız (km/h)');
            $table->decimal('max_speed', 5, 2)->nullable()->after('min_speed')->comment('Maksimum hız (km/h)');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_trip_locations', function (Blueprint $table) {
            $table->dropColumn('speed');
        });

        Schema::table('vehicle_trips', function (Blueprint $table) {
            $table->dropColumn(['gps_km', 'avg_speed', 'min_speed', 'max_speed']);
        });
    }
};
