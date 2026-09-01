<?php

namespace Database\Seeders;

use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Seeder;

class MissionSeeder extends Seeder
{
    public function run(): void
    {
        // Seleciona um usuário aleatório da família para associar as missões
        $userId = User::inRandomOrder()->first()->id;

        $missions = [
            [
                "description" => "Varrer a casa depois do almoço",
                "coins" => 10,
                "day" => "Domingo",
            ],
            [
                "description" => "Lavar a louça depois do jantar",
                "coins" => 10,
                "day" => "Segunda",
            ],
            [
                "description" => "Arrumar o quarto depois do almoço",
                "coins" => 0,
                "day" => "Sábado",
            ],
            [
                "description" => "Cuidar da Nina",
                "coins" => 30,
                "day" => null,
            ],
            [
                "description" => "Preparar almoço ou jantar para a família",
                "coins" => 30,
                "day" => null,
            ],
            [
                "description" => "Fazer simulado ou prova",
                "coins" => 20,
                "day" => null,
            ],
            [
                "description" => "Lista de exercícios da Luísa",
                "coins" => 20,
                "day" => "Sexta",
            ],
            [
                "description" => "Revisar aula do dia",
                "coins" => 30,
                "day" => null,
            ],
            [
                "description" => "Ser desrespeitosa ou grosseira",
                "coins" => -10,
                "day" => null,
            ],
            [
                "description" => "Retrucar mais de 2 vezes",
                "coins" => -10,
                "day" => null,
            ],
            [
                "description" => "Atrasar para compromisso",
                "coins" => -30,
                "day" => null,
            ],
            [
                "description" => "Acordar chamando até 2 vezes",
                "coins" => 50,
                "day" => null,
            ],
            [
                "description" => "Deixar a luz acesa",
                "coins" => -10,
                "day" => null,
            ]
        ];

        foreach ($missions as $mission) {
            $mission['user_id'] = $userId;
            Mission::create($mission);
        }
    }
}
