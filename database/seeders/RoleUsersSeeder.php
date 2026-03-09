<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleUsersSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create representative users for common roles
        $roles = [
            ['name' => 'Admin User', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Operator User', 'email' => 'operator@example.com', 'role' => 'operator'],
            ['name' => 'Parent User', 'email' => 'parent@example.com', 'role' => 'parent'],
            ['name' => 'Student User', 'email' => 'student@example.com', 'role' => 'student'],
        ];

        foreach ($roles as $r) {
            User::updateOrCreate(
                ['email' => $r['email']],
                [
                    'name' => $r['name'],
                    'password' => bcrypt('password'),
                    'role' => $r['role'],
                ]
            );
        }
    }
}
