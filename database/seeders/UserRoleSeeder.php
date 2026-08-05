<?php
// database/seeders/UserRoleSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin - Using updateOrCreate to prevent duplicates
        User::updateOrCreate(
            ['email' => 'admin@hopsurigao.com'],
            [
                'name' => 'Administrator',
                'phone' => '09123456789',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Create Operator
        User::updateOrCreate(
            ['email' => 'operator@hopsurigao.com'],
            [
                'name' => 'Tour Operator',
                'phone' => '09876543210',
                'password' => Hash::make('password123'),
                'role' => 'operator',
                'is_active' => true,
            ]
        );

        // Create Regular User
        User::updateOrCreate(
            ['email' => 'user@hopsurigao.com'],
            [
                'name' => 'Regular User',
                'phone' => '09123456780',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
            ]
        );

        // Create Additional Operators
        for ($i = 1; $i <= 2; $i++) {
            User::updateOrCreate(
                ['email' => "operator{$i}@hopsurigao.com"],
                [
                    'name' => "Operator {$i}",
                    'phone' => "0912345678{$i}",
                    'password' => Hash::make('password123'),
                    'role' => 'operator',
                    'is_active' => true,
                ]
            );
        }

        // Create Additional Users
        for ($i = 1; $i <= 5; $i++) {
            User::updateOrCreate(
                ['email' => "user{$i}@gmail.com"],
                [
                    'name' => "User {$i}",
                    'phone' => "0998765432{$i}",
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                    'is_active' => true,
                ]
            );
        }
    }
}