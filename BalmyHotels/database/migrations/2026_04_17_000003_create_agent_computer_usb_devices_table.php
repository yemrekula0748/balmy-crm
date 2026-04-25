<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_computer_usb_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')
                  ->constrained('agent_computers')
                  ->cascadeOnDelete();

            $table->string('friendly_name', 400)->nullable();
            $table->string('device_id', 300)->nullable();
            $table->string('type', 400)->nullable();
            $table->dateTime('first_connected')->nullable();
            $table->dateTime('last_connected')->nullable();
            $table->timestamp('first_seen_at')->nullable(); // İlk raporlandığı zaman

            $table->timestamps();

            $table->unique(['agent_computer_id', 'device_id']);
            $table->index('agent_computer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_computer_usb_devices');
    }
};
