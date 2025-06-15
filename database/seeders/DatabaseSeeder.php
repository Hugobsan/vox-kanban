<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primeiro, chama o seeder de roles para garantir que elas existam
        $this->call([
            RoleSeeder::class,
        ]);

        // Depois cria o usuário de teste
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Atribui role de admin ao usuário de teste
        $user->assignRole('admin');
    }
}
