<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_computer_security_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_computer_id')->unique();
            $table->foreign('agent_computer_id')
                  ->references('id')
                  ->on('agent_computers')
                  ->onDelete('cascade');
            $table->integer('alert_count')->default(0);
            $table->json('defender_threats')->nullable();
            $table->integer('shadow_copy_count')->nullable();
            $table->integer('failed_logins_1h')->nullable();
            $table->json('account_lockouts_1h')->nullable();
            $table->json('suspicious_processes')->nullable();
            $table->json('open_ports')->nullable();
            $table->json('usb_history')->nullable();
            $table->json('smb_signing')->nullable();
            $table->json('local_admins')->nullable();
            $table->json('windows_update')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_computer_security_snapshots');
    }
};
