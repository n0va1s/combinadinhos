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

        // (Os usuários padrão foram removidos conforme solicitado, permitindo o registro manual)

        $this->call([
            MissionSeeder::class,
            RewardSeeder::class,
        ]);

        // Associa todas as missões e recompensas criadas anteriormente à família
        \App\Models\Mission::whereNull('family_id')->update(['family_id' => $family->id]);
        \App\Models\Reward::whereNull('family_id')->update(['family_id' => $family->id]);
    }
}
