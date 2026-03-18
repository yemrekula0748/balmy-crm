<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('agency_code', 30)->unique();
            $table->string('name', 200);
            $table->enum('currency', ['TL', 'USD', 'EUR', 'GBP'])->default('TL');
            $table->text('billing_address');
            $table->json('nationalities')->nullable(); // array of country codes
            $table->enum('market', ['domestic', 'europe', 'middle_east', 'russia'])->default('domestic');
            $table->enum('payment_type', ['agency_pay', 'guest_pay'])->default('agency_pay');
            $table->enum('payment_method', ['city_ledger', 'cash', 'credit_card', 'bank_transfer'])->default('city_ledger');
            $table->enum('accommodation_type', ['sold', 'comp', 'house_use'])->default('sold');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
