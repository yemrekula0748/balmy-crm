<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_computer_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')->constrained('agent_computers')->cascadeOnDelete();
            $table->string('type');           // shutdown | restart | logoff | cmd | msgbox
            $table->text('payload')->nullable(); // cmd için komut metni, diğerleri için gecikme (sn)
            $table->enum('status', ['pending', 'sent', 'completed', 'failed'])->default('pending');
            $table->text('output')->nullable();  // agent'ten gelen sonuç
            $table->boolean('success')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('expires_at')->nullable();  // NULL = süre yok
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_computer_commands');
    }
};
