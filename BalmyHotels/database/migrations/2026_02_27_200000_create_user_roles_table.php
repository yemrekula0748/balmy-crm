<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_name', 50);
            $table->unique(['user_id', 'role_name']);
        });

        // Mevcut kullanıcıların rollerini pivot tabloya taşı
        $users = DB::table('users')->select('id', 'role')->get();
        foreach ($users as $u) {
            if ($u->role) {
                DB::table('user_roles')->insertOrIgnore([
                    'user_id'   => $u->id,
                    'role_name' => $u->role,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
