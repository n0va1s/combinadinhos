<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            [
                "description" => "50 reais",
                "cost" => 100,
            ],
            [
                "description" => "Trazer uma amiga pra casa",
                "cost" => 120,
            ],
            [
                "description" => "Jogar Roblox por até 2 horas",
                "cost" => 50,
            ],
            [
                "description" => "Usar o celular por mais 30 min",
                "cost" => 30,
            ],
            [
                "description" => "Escolher o cardápio de sexta",
                "cost" => 10,
            ],
            [
                "description" => "Escolher o filme",
                "cost" => 10,
            ]
        ];

        foreach ($rewards as $reward) {
            Reward::create($reward);
        }
    }
}
