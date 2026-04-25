<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_showcase_items', function (Blueprint $table) {
            // Add plain indexes to support the FK constraints before dropping the unique index
            $table->index('showcase_id', 'msi_showcase_id_idx');
            $table->index('qr_menu_id', 'msi_qr_menu_id_idx');
        });

        Schema::table('menu_showcase_items', function (Blueprint $table) {
            $table->dropUnique(['showcase_id', 'qr_menu_id']);
        });
    }

    public function down(): void
    {
        Schema::table('menu_showcase_items', function (Blueprint $table) {
            $table->unique(['showcase_id', 'qr_menu_id']);
            $table->dropIndex('msi_showcase_id_idx');
            $table->dropIndex('msi_qr_menu_id_idx');
        });
    }
};
