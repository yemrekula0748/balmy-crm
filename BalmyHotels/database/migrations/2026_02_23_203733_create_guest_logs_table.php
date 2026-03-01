<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guest_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');               // Misafir adı
            $table->string('room_no')->nullable();      // Oda numarası (opsiyonel)
            $table->string('id_no')->nullable();        // TC / Pasaport No
            $table->string('nationality')->default('Türk'); // Uyruk
            $table->string('phone')->nullable();        // Telefon
            $table->enum('type', ['check_in', 'check_out']); // Giriş / Çıkış
            $table->timestamp('logged_at');             // Kayıt zamanı
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_logs');
    }
};
