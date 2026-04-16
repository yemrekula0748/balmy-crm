<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_computer_deletions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')
                  ->constrained('agent_computers')
                  ->cascadeOnDelete();

            $table->string('name', 500)->nullable();        // Dosya adı
            $table->string('path', 1000)->nullable();       // Tam dosya yolu
            $table->string('directory', 1000)->nullable();  // Klasör yolu
            $table->unsignedBigInteger('size')->nullable(); // Byte cinsinden boyut
            $table->dateTime('modified_at')->nullable();    // Dosyanın son değiştirilme zamanı
            $table->dateTime('deleted_at');                 // Silinme zamanı

            $table->timestamps();

            $table->index('agent_computer_id');
            $table->index('deleted_at');
            $table->index(['agent_computer_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_computer_deletions');
    }
};
