<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::drop('guest_logs');

        Schema::create('guest_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete(); // Kime geliyor
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();   // Kaydı kim yaptı

            // Ziyaretçi bilgileri
            $table->string('visitor_name');
            $table->string('visitor_phone')->nullable();
            $table->string('visitor_id_no')->nullable();       // TC / Pasaport
            $table->string('visitor_company')->nullable();     // Kurum / Şirket

            // Ziyaret detayı
            $table->enum('purpose', ['meeting', 'delivery', 'interview', 'official', 'other'])->default('meeting');
            $table->string('purpose_note')->nullable();        // Amaç açıklaması

            // Zaman
            $table->timestamp('check_in_at');
            $table->timestamp('check_out_at')->nullable();     // null = hâlâ içeride

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('guest_logs');

        Schema::create('guest_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('room_no')->nullable();
            $table->string('id_no')->nullable();
            $table->string('nationality')->default('Türk');
            $table->string('phone')->nullable();
            $table->enum('type', ['check_in', 'check_out']);
            $table->timestamp('logged_at');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }
};
