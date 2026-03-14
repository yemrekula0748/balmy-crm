<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('hotel_name');
            $table->string('place_id');
            $table->decimal('rating', 3, 1)->nullable();
            $table->unsignedInteger('user_ratings_total')->default(0);
            $table->date('snapshot_date');
            $table->timestamps();

            $table->unique(['place_id', 'snapshot_date']); // Günde 1 kayıt
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_snapshots');
    }
};
