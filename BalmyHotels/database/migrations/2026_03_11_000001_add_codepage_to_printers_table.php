<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            // ESC/POS codepage numarası (varsayılan 32 = PC1254 Windows Turkish)
            $table->unsignedTinyInteger('codepage')->default(32)->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->dropColumn('codepage');
        });
    }
};
