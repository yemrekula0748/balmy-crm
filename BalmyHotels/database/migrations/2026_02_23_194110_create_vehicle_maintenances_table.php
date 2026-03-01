<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('servis'); // servis|lastik|yag|egzoz|fren|diger
            $table->date('maintenance_at');            // bakım tarihi
            $table->integer('km');                     // bakım yapıldığındaki km
            $table->integer('next_km')->nullable();    // sonraki bakım km
            $table->date('next_date')->nullable();     // sonraki bakım tarihi
            $table->string('service_name')->nullable(); // Servis adı
            $table->decimal('cost', 10, 2)->nullable(); // Tutar
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenances');
    }
};
