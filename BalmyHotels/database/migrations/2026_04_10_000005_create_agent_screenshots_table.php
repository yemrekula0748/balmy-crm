<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_screenshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_computer_id');
            $table->unsignedBigInteger('command_id')->nullable();
            $table->string('image_path');
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->foreign('agent_computer_id')
                  ->references('id')->on('agent_computers')
                  ->onDelete('cascade');

            $table->foreign('command_id')
                  ->references('id')->on('agent_computer_commands')
                  ->onDelete('set null');

            $table->index(['agent_computer_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_screenshots');
    }
};
