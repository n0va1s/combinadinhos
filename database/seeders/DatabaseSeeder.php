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
        $family = Family::create([
            'name' => 'Família Silva'
        ]);

        // Cria usuários solicitados com as respectivas senhas
        User::create([
            'name' => 'Pai',
            'email' => 'pai@email.com',
            'password' => Hash::make('localhost@1'),
            'role' => UserRole::PAI,
            'family_id' => $family->id,
            'balance' => 0
        ]);

        User::create([
            'name' => 'Mãe',
            'email' => 'mae@email.com',
            'password' => Hash::make('localhost@1'),
            'role' => UserRole::MAE,
            'family_id' => $family->id,
            'balance' => 0
        ]);

        User::create([
            'name' => 'Filha',
            'email' => 'filha@email.com',
            'password' => Hash::make('localhost@1'),
            'role' => UserRole::FILHA,
            'family_id' => $family->id,
            'balance' => 50
        ]);

        $this->call([
            MissionSeeder::class,
            RewardSeeder::class,
        ]);

        // Associa todas as missões e recompensas criadas anteriormente à família
        \App\Models\Mission::whereNull('family_id')->update(['family_id' => $family->id]);
        \App\Models\Reward::whereNull('family_id')->update(['family_id' => $family->id]);
    }
}
