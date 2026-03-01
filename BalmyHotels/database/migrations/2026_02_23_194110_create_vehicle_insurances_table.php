<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('type');                    // trafik | kasko
            $table->string('company');                 // Sigorta şirketi
            $table->string('policy_no');               // Poliçe No
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('cost', 10, 2)->nullable(); // Prim tutarı
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_insurances');
    }
};
