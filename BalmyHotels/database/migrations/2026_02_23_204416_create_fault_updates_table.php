<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fault_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fault_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('note');
            $table->enum('status_from', ['open', 'in_progress', 'resolved', 'closed'])->nullable();
            $table->enum('status_to',   ['open', 'in_progress', 'resolved', 'closed'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fault_updates');
    }
};
