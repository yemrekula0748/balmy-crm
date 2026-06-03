<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropBranchForeignKeyIfExists();

        DB::statement('ALTER TABLE shuttle_vehicles MODIFY branch_id BIGINT UNSIGNED NULL');
        DB::table('shuttle_vehicles')->update(['branch_id' => null]);

        Schema::table('shuttle_vehicles', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $this->dropBranchForeignKeyIfExists();

        $fallbackBranchId = DB::table('branches')->orderBy('id')->value('id');

        if ($fallbackBranchId) {
            DB::table('shuttle_vehicles')
                ->whereNull('branch_id')
                ->update(['branch_id' => $fallbackBranchId]);
        }

        DB::statement('ALTER TABLE shuttle_vehicles MODIFY branch_id BIGINT UNSIGNED NOT NULL');

        Schema::table('shuttle_vehicles', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
        });
    }

    private function dropBranchForeignKeyIfExists(): void
    {
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'shuttle_vehicles'
              AND COLUMN_NAME = 'branch_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($constraints as $constraint) {
            $constraintName = str_replace('`', '``', $constraint->CONSTRAINT_NAME);
            DB::statement("ALTER TABLE shuttle_vehicles DROP FOREIGN KEY `{$constraintName}`");
        }
    }
};
