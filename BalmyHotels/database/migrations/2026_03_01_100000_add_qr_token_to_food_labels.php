<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_labels', function (Blueprint $table) {
            $table->uuid('qr_token')->nullable()->unique()->after('id');
        });

        // Mevcut kayıtlara token üret (chunkById ile offset kayması olmaz)
        DB::table('food_labels')->whereNull('qr_token')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                DB::table('food_labels')
                    ->where('id', $row->id)
                    ->update(['qr_token' => (string) Str::uuid()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('food_labels', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
