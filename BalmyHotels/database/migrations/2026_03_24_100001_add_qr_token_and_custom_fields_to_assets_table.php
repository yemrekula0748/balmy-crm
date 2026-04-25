<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('photo');
            $table->json('custom_fields')->nullable()->after('properties');
        });

        // Populate qr_token for existing assets
        \DB::table('assets')->whereNull('qr_token')->orderBy('id')->each(function ($asset) {
            \DB::table('assets')->where('id', $asset->id)->update([
                'qr_token' => Str::uuid()->toString(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'custom_fields']);
        });
    }
};
