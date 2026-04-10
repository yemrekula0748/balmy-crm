<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // En son frame'i saklar — her bilgisayar için tek satır (upsert)
        Schema::create('vnc_frames', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_computer_id');
            $table->longText('image_data');   // base64 JPEG
            $table->unsignedSmallInteger('screen_w')->default(1920);
            $table->unsignedSmallInteger('screen_h')->default(1080);
            $table->unsignedInteger('seq')->default(0);
            $table->timestamp('updated_at')->nullable();

            $table->foreign('agent_computer_id')
                  ->references('id')->on('agent_computers')
                  ->onDelete('cascade');

            $table->unique('agent_computer_id');
        });

        // Tarayıcıdan agent'a gönderilen input kuyruğu
        Schema::create('vnc_inputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_computer_id');
            $table->json('events');
            $table->boolean('consumed')->default(false);
            $table->timestamps();

            $table->foreign('agent_computer_id')
                  ->references('id')->on('agent_computers')
                  ->onDelete('cascade');

            $table->index(['agent_computer_id', 'consumed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vnc_inputs');
        Schema::dropIfExists('vnc_frames');
    }
};
