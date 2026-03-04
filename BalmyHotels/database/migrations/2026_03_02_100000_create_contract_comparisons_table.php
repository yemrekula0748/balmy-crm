<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();          // İsteğe bağlı etiket
            $table->string('file_a_name');               // Orijinal dosya adı A
            $table->string('file_b_name');               // Orijinal dosya adı B
            $table->string('file_a_type');               // pdf | docx
            $table->string('file_b_type');
            $table->integer('lines_added')->default(0);
            $table->integer('lines_removed')->default(0);
            $table->integer('lines_equal')->default(0);
            $table->unsignedTinyInteger('similarity')->default(0); // 0-100 %
            $table->longText('diff_json');               // Fark verisi JSON
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_comparisons');
    }
};
