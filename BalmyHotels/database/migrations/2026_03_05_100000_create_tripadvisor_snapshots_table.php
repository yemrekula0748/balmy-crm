<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tripadvisor_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('hotel_name');
            $table->unsignedInteger('location_id');
            $table->decimal('rating', 3, 1)->nullable();
            $table->unsignedInteger('num_reviews')->default(0);
            $table->string('ranking_string')->nullable();
            $table->date('snapshot_date');
            $table->timestamps();

            $table->unique(['location_id', 'snapshot_date']); // Günde bir kayıt
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tripadvisor_snapshots');
    }
};
