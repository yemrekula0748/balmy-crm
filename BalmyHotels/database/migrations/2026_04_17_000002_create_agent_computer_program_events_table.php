<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_computer_program_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')
                  ->constrained('agent_computers')
                  ->cascadeOnDelete();

            $table->enum('event_type', ['installed', 'removed']);
            $table->string('program_name', 500);
            $table->string('version', 200)->nullable();
            $table->string('publisher', 300)->nullable();
            $table->dateTime('detected_at');

            $table->timestamps();

            $table->index('agent_computer_id');
            $table->index('detected_at');
            $table->index(['agent_computer_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_computer_program_events');
    }
};
