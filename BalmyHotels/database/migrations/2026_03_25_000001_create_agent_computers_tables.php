<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ----------------------------------------------------------------
        // a) agent_computers
        // ----------------------------------------------------------------
        Schema::create('agent_computers', function (Blueprint $table) {
            $table->id();
            $table->string('machine_guid', 100)->unique();
            $table->string('hostname', 255);
            $table->string('os_product_name', 255)->nullable();
            $table->string('os_version', 100)->nullable();
            $table->string('os_release', 50)->nullable();
            $table->string('os_build_number', 50)->nullable();
            $table->string('os_architecture', 20)->nullable();
            $table->date('os_install_date')->nullable();
            $table->string('os_registered_owner', 255)->nullable();
            $table->string('os_serial_number', 100)->nullable();
            $table->boolean('is_domain_joined')->default(false);
            $table->string('domain_name', 255)->nullable();
            $table->string('workgroup_name', 255)->nullable();
            $table->string('domain_controller', 255)->nullable();
            $table->json('current_users')->nullable();
            $table->datetime('last_boot_time')->nullable();
            $table->string('agent_version', 20)->nullable();
            $table->datetime('reported_at')->nullable();
            $table->datetime('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('hostname');
            $table->index('last_seen_at');
            $table->index('is_domain_joined');
        });

        // ----------------------------------------------------------------
        // b) agent_computer_hardware
        // ----------------------------------------------------------------
        Schema::create('agent_computer_hardware', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')->constrained('agent_computers')->cascadeOnDelete();
            $table->string('cpu_name', 255)->nullable();
            $table->unsignedSmallInteger('cpu_cores_physical')->nullable();
            $table->unsignedSmallInteger('cpu_cores_logical')->nullable();
            $table->unsignedInteger('cpu_speed_mhz')->nullable();
            $table->float('cpu_usage_percent')->nullable();
            $table->float('total_ram_gb')->nullable();
            $table->float('available_ram_gb')->nullable();
            $table->float('ram_usage_percent')->nullable();
            $table->json('ram_slots')->nullable();
            $table->string('motherboard', 255)->nullable();
            $table->string('bios_version', 100)->nullable();
            $table->string('bios_date', 20)->nullable();
            $table->timestamps();
        });

        // ----------------------------------------------------------------
        // c) agent_computer_network_adapters
        // ----------------------------------------------------------------
        Schema::create('agent_computer_network_adapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')->constrained('agent_computers')->cascadeOnDelete();
            $table->string('adapter_name', 255);
            $table->string('mac_address', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('ip_address_v6', 50)->nullable();
            $table->string('subnet_mask', 45)->nullable();
            $table->string('gateway', 45)->nullable();
            $table->json('dns_servers')->nullable();
            $table->boolean('dhcp_enabled')->nullable();
            $table->string('dhcp_server', 45)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('agent_computer_id');
            $table->index('ip_address');
            $table->index('mac_address');
        });

        // ----------------------------------------------------------------
        // d) agent_computer_disks
        // ----------------------------------------------------------------
        Schema::create('agent_computer_disks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')->constrained('agent_computers')->cascadeOnDelete();
            $table->string('drive_letter', 10);
            $table->string('mount_point', 255)->nullable();
            $table->string('filesystem', 20)->nullable();
            $table->string('label', 255)->nullable();
            $table->float('total_space_gb')->nullable();
            $table->float('used_space_gb')->nullable();
            $table->float('free_space_gb')->nullable();
            $table->float('usage_percent')->nullable();
            $table->timestamps();

            $table->index('agent_computer_id');
        });

        // ----------------------------------------------------------------
        // e) agent_computer_antivirus
        // ----------------------------------------------------------------
        Schema::create('agent_computer_antivirus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')->constrained('agent_computers')->cascadeOnDelete();
            $table->string('product_name', 255);
            $table->string('product_state_raw', 20)->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_up_to_date')->default(false);
            $table->string('timestamp', 100)->nullable();
            $table->timestamps();

            $table->index('agent_computer_id');
        });

        // ----------------------------------------------------------------
        // f) agent_computer_security
        // ----------------------------------------------------------------
        Schema::create('agent_computer_security', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')->constrained('agent_computers')->cascadeOnDelete()->unique();
            $table->boolean('rdp_enabled')->nullable();
            $table->boolean('uac_enabled')->nullable();
            $table->boolean('firewall_domain')->nullable();
            $table->boolean('firewall_private')->nullable();
            $table->boolean('firewall_public')->nullable();
            $table->tinyInteger('auto_update')->nullable();
            $table->string('last_windows_update', 100)->nullable();
            $table->json('bitlocker')->nullable();
            $table->timestamps();
        });

        // ----------------------------------------------------------------
        // g) agent_computer_installed_programs
        // ----------------------------------------------------------------
        Schema::create('agent_computer_installed_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')->constrained('agent_computers')->cascadeOnDelete();
            $table->string('name', 500);
            $table->string('version', 100)->nullable();
            $table->string('publisher', 255)->nullable();
            $table->string('install_date', 20)->nullable();
            $table->string('install_location', 500)->nullable();
            $table->timestamps();

            $table->index('agent_computer_id');
            $table->index('name');
            $table->index(['agent_computer_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_computer_installed_programs');
        Schema::dropIfExists('agent_computer_security');
        Schema::dropIfExists('agent_computer_antivirus');
        Schema::dropIfExists('agent_computer_disks');
        Schema::dropIfExists('agent_computer_network_adapters');
        Schema::dropIfExists('agent_computer_hardware');
        Schema::dropIfExists('agent_computers');
    }
};
