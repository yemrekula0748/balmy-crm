<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_products', function (Blueprint $table) {
            $table->foreignId('printer_id')->nullable()->after('food_category_id')->constrained('printers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('food_products', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Printer::class);
            $table->dropColumn('printer_id');
        });
    }
};
