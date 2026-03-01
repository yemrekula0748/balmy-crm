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
        Schema::create('faults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete(); // bildiren kişi
            $table->foreignId('assigned_department_id')->constrained('departments')->cascadeOnDelete(); // ilgili departman
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // atanan kişi
            $table->string('title');                     // Arıza başlığı
            $table->text('description');                 // Açıklama
            $table->string('location')->nullable();      // Konum (oda 101, resepsiyon vb.)
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faults');
    }
};
