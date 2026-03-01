<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Tür: giris | cikis | goreve_gidis | gorevden_gelis
            $table->string('type');
            $table->integer('km');                     // operasyon anındaki km
            $table->dateTime('operation_at');          // işlem zamanı
            $table->string('destination')->nullable(); // Göreve gittiği yer
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_operations');
    }
};
