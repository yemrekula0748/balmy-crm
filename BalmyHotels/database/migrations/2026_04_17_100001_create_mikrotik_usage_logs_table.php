<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64);
            $table->string('mac_address', 17);
            $table->string('ip_address', 45)->nullable();
            $table->unsignedBigInteger('bytes_in')->default(0);
            $table->unsignedBigInteger('bytes_out')->default(0);
            $table->dateTime('session_started_at');
            $table->dateTime('last_seen_at');
            $table->timestamps();

            // Bir oturumu benzersiz tanımlamak için
            $table->unique(['username', 'mac_address', 'session_started_at'], 'mikrotik_session_unique');
            $table->index('username');
            $table->index('session_started_at');
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_usage_logs');
    }
};
