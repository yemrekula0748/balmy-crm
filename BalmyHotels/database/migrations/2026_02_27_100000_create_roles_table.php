<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();          // slug: super_admin, branch_manager...
            $table->string('display_name', 100);
            $table->string('color', 20)->default('primary'); // badge rengi
            $table->boolean('is_system')->default(false);  // sistem rolü silinemez
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
