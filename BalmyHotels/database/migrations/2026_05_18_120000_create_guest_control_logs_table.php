<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_control_logs', function (Blueprint $table) {
            $table->id();
            $table->string('hotel_key', 50);
            $table->string('hotel_name');
            $table->unsignedBigInteger('hotel_id');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('room_no', 20);
            $table->string('action_type', 20);
            $table->string('selection_key', 191);
            $table->string('stay_signature', 64);
            $table->string('api_guest_id', 100)->nullable();
            $table->string('api_reservation_id', 100)->nullable();
            $table->string('api_reservation_name_id', 100)->nullable();
            $table->string('first_name', 120)->nullable();
            $table->string('last_name', 120)->nullable();
            $table->string('full_name');
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('national_id_no', 100)->nullable();
            $table->string('passport_no', 100)->nullable();
            $table->date('hotel_checkin_date')->nullable();
            $table->date('hotel_checkout_date')->nullable();
            $table->string('arrival_time', 20)->nullable();
            $table->string('departure_time', 20)->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_at');
            $table->timestamps();

            $table->unique(
                ['hotel_id', 'action_type', 'stay_signature'],
                'guest_control_logs_unique_action'
            );
            $table->index(['hotel_key', 'room_no']);
            $table->index(['action_type', 'action_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_control_logs');
    }
};
