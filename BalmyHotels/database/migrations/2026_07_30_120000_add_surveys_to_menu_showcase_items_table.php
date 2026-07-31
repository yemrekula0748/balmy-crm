<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_showcase_items', function (Blueprint $table) {
            $table->foreignId('survey_id')
                ->nullable()
                ->after('qr_menu_id')
                ->constrained('surveys')
                ->cascadeOnDelete();
        });

        $this->makeQrMenuNullable();
    }

    public function down(): void
    {
        DB::table('menu_showcase_items')->whereNull('qr_menu_id')->delete();

        Schema::table('menu_showcase_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('survey_id');
        });

        $this->makeQrMenuRequired();
    }

    private function makeQrMenuNullable(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE menu_showcase_items MODIFY qr_menu_id BIGINT UNSIGNED NULL');
            return;
        }

        Schema::table('menu_showcase_items', function (Blueprint $table) {
            $table->unsignedBigInteger('qr_menu_id')->nullable()->change();
        });
    }

    private function makeQrMenuRequired(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE menu_showcase_items MODIFY qr_menu_id BIGINT UNSIGNED NOT NULL');
            return;
        }

        Schema::table('menu_showcase_items', function (Blueprint $table) {
            $table->unsignedBigInteger('qr_menu_id')->nullable(false)->change();
        });
    }
};
