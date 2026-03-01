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
        Schema::create('asset_exits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches');
            $table->enum('taker_type', ['staff', 'guest']);   // Kim aldı
            // staff bazlı
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            // guest bazlı
            $table->string('guest_name')->nullable();
            $table->string('guest_room')->nullable();
            $table->string('guest_id_no')->nullable();
            $table->string('guest_phone')->nullable();
            $table->text('purpose');                          // Çıkış amacı
            $table->timestamp('taken_at');
            $table->timestamp('expected_return_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'returned'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_exits');
    }
};
