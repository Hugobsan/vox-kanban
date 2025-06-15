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
        // Define the roles to be created (apenas user e admin)
        $roles = [
            ['name' => 'admin'],
            ['name' => 'user'],
        ];

        // Insert roles into the database
        foreach ($roles as $role) {
            \App\Models\Role::create($role);
        }
    }
}
