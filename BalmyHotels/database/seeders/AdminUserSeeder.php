<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@balmy.com'],
            [
                'name'       => 'Super Admin',
                'password'   => Hash::make('12345'),
                'role'       => 'super_admin',
                'is_active'  => true,
            ]
        );

        // user_roles pivot tablosuna da ekle (rol kontrolü buradan yapılıyor)
        UserRole::updateOrCreate(
            ['user_id' => $user->id, 'role_name' => 'super_admin']
        );
    }
}
