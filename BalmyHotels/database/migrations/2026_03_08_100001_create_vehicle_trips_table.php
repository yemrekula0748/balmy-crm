<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('start_km');
            $table->integer('end_km')->nullable();
            $table->string('start_km_photo');           // Fotoğraf yolu
            $table->string('end_km_photo')->nullable();
            $table->string('destination');              // Gidilecek yer
            $table->text('notes')->nullable();          // Görev notu
            $table->string('status')->default('active'); // active | completed
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_trips');
    }
};
