<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $operatorRole = Role::where('name', 'Operator')->first();
        $touristRole = Role::where('name', 'Tourist')->first();

        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role_id' => $adminRole?->id,
                'is_protected' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'operator@gmail.com'],
            [
                'name' => 'Operator User',
                'password' => Hash::make('password'),
                'role_id' => $operatorRole?->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'tourist@gmail.com'],
            [
                'name' => 'Tourist User',
                'password' => Hash::make('password'),
                'role_id' => $touristRole?->id,
            ]
        );
    }
}