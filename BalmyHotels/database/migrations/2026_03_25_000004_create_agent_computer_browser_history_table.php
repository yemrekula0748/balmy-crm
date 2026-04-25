<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_computer_browser_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_computer_id')
                  ->constrained('agent_computers')
                  ->cascadeOnDelete();

            $table->string('username', 255);             // Windows kullanıcı adı
            $table->string('browser', 50);               // chrome | edge | firefox | brave | opera | vivaldi
            $table->string('profile', 100)->nullable();  // Default | Profile 1 | ...

            $table->text('url');                         // Tam URL
            $table->string('title', 500)->nullable();    // Sayfa başlığı
            $table->dateTime('visit_time');              // Ziyaret zamanı
            $table->unsignedSmallInteger('visit_count')->default(1);

            $table->timestamps();

            $table->index('agent_computer_id');
            $table->index('username');
            $table->index('visit_time');
            $table->index(['agent_computer_id', 'username', 'visit_time'], 'acbh_computer_user_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_computer_browser_history');
    }
};
