<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ----------------------------------------------------------------
        // agent_computer_mail  (per-computer, unique)
        // ----------------------------------------------------------------
        Schema::create('agent_computer_mail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')
                  ->unique()
                  ->constrained('agent_computers')
                  ->cascadeOnDelete();
            $table->string('default_mail_client', 255)->nullable();
            $table->string('default_mail_progid', 255)->nullable();
            $table->boolean('is_new_outlook')->default(false);
            $table->string('outlook_version', 255)->nullable();
            $table->timestamps();
        });

        // ----------------------------------------------------------------
        // agent_computer_mail_accounts  (one-to-many)
        // ----------------------------------------------------------------
        Schema::create('agent_computer_mail_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')
                  ->constrained('agent_computers')
                  ->cascadeOnDelete();
            $table->string('smtp_address', 255)->nullable();
            $table->string('display_name', 255)->nullable();
            $table->string('account_type', 100)->nullable();
            $table->string('exchange_server', 255)->nullable();
            $table->string('source', 100)->nullable();
            $table->timestamps();

            $table->index('agent_computer_id');
            $table->index('smtp_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_computer_mail_accounts');
        Schema::dropIfExists('agent_computer_mail');
    }
};
