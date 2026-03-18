<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_code', 30)->unique();
            $table->foreignId('agency_id')->constrained('agencies')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price_single', 10, 2)->default(0);
            $table->decimal('price_double', 10, 2)->default(0);
            $table->decimal('price_triple', 10, 2)->default(0);
            $table->decimal('price_quad', 10, 2)->default(0);
            $table->decimal('price_baby1', 10, 2)->default(0);
            $table->decimal('price_baby2', 10, 2)->default(0);
            $table->decimal('price_child1', 10, 2)->default(0);
            $table->decimal('price_child2', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_contracts');
    }
};
