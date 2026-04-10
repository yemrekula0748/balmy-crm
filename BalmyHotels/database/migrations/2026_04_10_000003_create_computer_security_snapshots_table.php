<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computer_security_snapshots', function (Blueprint $table) {
            $table->id();
            // Manuel envanter ile bağlantı (IP eşleşmesi ile doldurulur)
            $table->foreignId('computer_id')
                  ->nullable()
                  ->constrained('computers')
                  ->nullOnDelete();
            // Ajanla bağlantı (hangi agent_computer kaydından geldi)
            $table->foreignId('agent_computer_id')
                  ->nullable()
                  ->constrained('agent_computers')
                  ->nullOnDelete();

            $table->integer('alert_count')->default(0);

            // Tehdit verileri
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

            // Genişletilmiş alanlar
            $table->json('new_services_24h')->nullable();
            $table->json('account_changes_24h')->nullable();
            $table->json('unexpected_shutdowns')->nullable();
            $table->json('rdp_events_24h')->nullable();
            $table->json('scheduled_tasks_24h')->nullable();
            $table->json('admin_logins_1h')->nullable();
            $table->json('service_crashes_24h')->nullable();

            $table->timestamp('reported_at')->nullable();
            $table->timestamps();

            $table->index(['computer_id', 'reported_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computer_security_snapshots');
    }
};
