<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Usuário teste - Motorista
        User::firstOrCreate(
            ['email' => 'teste@teste.com'],
            [
                'name' => 'João Motorista',
                'email' => 'teste@teste.com',
                'password' => Hash::make('123456'),
                'role' => 'driver',
                'phone' => '(11) 99999-1111',
            ]
        );

        // Usuário VG - Passageiro  
        User::firstOrCreate(
            ['email' => 'vg@teste.com'],
            [
                'name' => 'Victor Passageiro',
                'email' => 'vg@teste.com',
                'password' => Hash::make('123456'),
                'role' => 'passenger',
                'phone' => '(11) 99999-2222',
            ]
        );

        // Usuário Both - Motorista e Passageiro
        User::firstOrCreate(
            ['email' => 'both@teste.com'],
            [
                'name' => 'Maria Both',
                'email' => 'both@teste.com',
                'password' => Hash::make('123456'),
                'role' => 'both',
                'phone' => '(11) 99999-3333',
            ]
        );

        $this->command->info('Usuários de teste criados:');
        $this->command->info('- teste@teste.com (senha: 123456) - MOTORISTA');
        $this->command->info('- vg@teste.com (senha: 123456) - PASSAGEIRO');
        $this->command->info('- both@teste.com (senha: 123456) - AMBOS');
    }
}
