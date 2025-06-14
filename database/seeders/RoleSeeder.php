<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the roles to be created
        $roles = [
            ['name' => 'admin'],
            ['name' => 'moderator'],
            ['name' => 'support'],
            ['name' => 'finance'],
            ['name' => 'user'],
            ['name' => 'premium'],
            ['name' => 'banned']
        ];

        // Insert roles into the database
        foreach ($roles as $role) {
            \App\Models\Role::create($role);
        }
    }
}
