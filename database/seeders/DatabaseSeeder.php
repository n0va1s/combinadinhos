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

        $filho = \App\Models\User::create([
            'name' => 'Filha',
            'email' => 'filha@email.com',
            'role' => \App\Enums\UserRole::FILHA,
            'password' => Hash::make('localhost@1'),
            'family_id' => $family->id,
        ]);

        $pai = \App\Models\User::create([
            'name' => 'Pai',
            'email' => 'pai@email.com',
            'role' => \App\Enums\UserRole::PAI,
            'password' => Hash::make('localhost@1'),
            'family_id' => $family->id,
        ]);

        $mae = \App\Models\User::create([
            'name' => 'Mãe',
            'email' => 'mae@email.com',
            'role' => \App\Enums\UserRole::MAE,
            'password' => Hash::make('localhost@1'),
            'family_id' => $family->id,
        ]);

        // (Os usuários padrão foram removidos conforme solicitado, permitindo o registro manual)

        $this->call([
            MissionSeeder::class,
            RewardSeeder::class,
        ]);

        // Associa todas as missões e recompensas criadas anteriormente à família


    }
}
