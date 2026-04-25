<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_computer_file_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')
                  ->constrained('agent_computers')
                  ->cascadeOnDelete();

            // Windows Event Log alanları
            $table->unsignedSmallInteger('event_id');          // 4663 veya 4660
            $table->dateTime('event_time');                    // Olayın gerçekleştiği zaman
            $table->string('subject_user', 255)->nullable();   // Kim sildi
            $table->string('subject_domain', 100)->nullable();
            $table->string('subject_logon_id', 50)->nullable();

            // Nesne bilgileri
            $table->string('object_name', 1000)->nullable();   // Tam dosya yolu
            $table->string('object_type', 50)->nullable();     // "File"
            $table->string('process_name', 500)->nullable();   // Hangi uygulama sildi

            // Erişim bilgisi
            $table->string('access_mask', 20)->nullable();     // örn. "0x10000"
            $table->string('handle_id', 30)->nullable();       // 4663 + 4660 eşleştirmek için

            $table->timestamps();

            $table->index('agent_computer_id');
            $table->index('event_time');
            $table->index('subject_user');
            $table->index(['agent_computer_id', 'event_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_computer_file_events');
    }
};
