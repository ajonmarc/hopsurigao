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
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role_id' => $adminRole?->id,
                'is_protected' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'operator@example.com'],
            [
                'name' => 'Operator User',
                'password' => Hash::make('password'),
                'role_id' => $operatorRole?->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'tourist@example.com'],
            [
                'name' => 'Tourist User',
                'password' => Hash::make('password'),
                'role_id' => $touristRole?->id,
            ]
        );

        // Generate 200 additional random users, split between Operator and Tourist roles
        $randomRoleIds = collect([$operatorRole?->id, $touristRole?->id])
            ->filter()
            ->values();

        User::factory()
            ->count(200)
            ->make() // build without saving so we can assign role_id per-user
            ->each(function (User $user) use ($randomRoleIds) {
                $user->role_id = $randomRoleIds->isNotEmpty()
                    ? $randomRoleIds->random()
                    : null;
                $user->save();
            });
    }
}