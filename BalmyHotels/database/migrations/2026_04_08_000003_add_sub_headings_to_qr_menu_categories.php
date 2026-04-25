<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_menu_categories', function (Blueprint $table) {
            // JSON array of multilingual sub-heading objects [{tr:...,en:...,de:...}, ...]
            $table->json('sub_headings')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('qr_menu_categories', function (Blueprint $table) {
            $table->dropColumn('sub_headings');
        });
    }
};
