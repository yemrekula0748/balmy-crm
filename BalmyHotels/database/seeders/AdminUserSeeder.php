<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@balmy.com'],
            [
                'name'       => 'Super Admin',
                'password'   => Hash::make('12345'),
                'role'       => 'super_admin',
                'is_active'  => true,
            ]
        );
    }
}
