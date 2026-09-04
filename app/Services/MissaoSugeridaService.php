<?php

namespace App\Services;

use App\Models\User;
use App\Models\Mission;
use Illuminate\Support\Facades\DB;

class MissaoSugeridaService
{
    /**
     * Vincula uma lista de missões sugeridas ao usuário filho dentro de uma transação.
     *
     * @param User $usuarioFilho
     * @param array<int, array{descricao: string, moedas: int, dia?: ?string}> $missoes
     * @return int Quantidade de missões criadas com sucesso
     */
    public function vincularMissoesAoUsuario(User $usuarioFilho, array $missoes): int
    {
        if (empty($missoes)) {
            return 0;
        }

        return DB::transaction(function () use ($usuarioFilho, $missoes) {
            $quantidadeCriada = 0;

            foreach ($missoes as $missao) {
                if (empty($missao['descricao'])) {
                    continue;
                }

                Mission::create([
                    'user_id' => $usuarioFilho->id,
                    'description' => $missao['descricao'],
                    'coins' => (int) ($missao['moedas'] ?? 10),
                    'day' => !empty($missao['dia']) ? $missao['dia'] : null,
                ]);

                $quantidadeCriada++;
            }

            return $quantidadeCriada;
        });
    }
}
