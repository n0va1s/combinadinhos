<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Family;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cria uma família default
        $familia = Family::create([
            'name' => 'Família Silva'
        ]);

        $filha = User::create([
            'name' => 'Filha',
            'email' => 'filha@email.com',
            'role' => UserRole::FILHA,
            'birth_date' => now()->subYears(10)->toDateString(),
            'password' => Hash::make('localhost@1'),
            'family_id' => $familia->id,
        ]);

        // Filha Mais Nova sem missões cadastradas para teste de playlists
        $filhaMaisNova = User::create([
            'name' => 'Filha Mais Nova',
            'email' => 'filhamaisnova@email.com',
            'role' => UserRole::FILHA,
            'birth_date' => now()->subYears(4)->toDateString(),
            'password' => Hash::make('localhost@1'),
            'family_id' => $familia->id,
        ]);

        $pai = User::create([
            'name' => 'Pai',
            'email' => 'pai@email.com',
            'role' => UserRole::PAI,
            'password' => Hash::make('localhost@1'),
            'family_id' => $familia->id,
        ]);

        $mae = User::create([
            'name' => 'Mãe',
            'email' => 'mae@email.com',
            'role' => UserRole::MAE,
            'password' => Hash::make('localhost@1'),
            'family_id' => $familia->id,
        ]);

        $this->call([
            MissionSeeder::class,
            RewardSeeder::class,
        ]);
       
    }
}
