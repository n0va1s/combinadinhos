<?php

namespace Tests\Feature;

use App\Enums\PlaylistMissao;
use App\Enums\UserRole;
use App\Models\Mission;
use App\Models\User;
use App\Services\MissaoSugeridaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissaoSugeridaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_vincula_missoes_sugeridas_ao_usuario_com_sucesso(): void
    {
        $filho = User::create([
            'name' => 'Lucas',
            'email' => 'lucas@exemplo.com',
            'role' => UserRole::FILHO,
            'birth_date' => now()->subYears(7)->toDateString(),
            'balance' => 0,
        ]);

        $servico = new MissaoSugeridaService();
        $missoes = PlaylistMissao::EXPLORADORES_ROTINA->missoes();

        $quantidadeCriada = $servico->vincularMissoesAoUsuario($filho, $missoes);

        $this->assertSame(count($missoes), $quantidadeCriada);
        $this->assertDatabaseCount('missions', count($missoes));

        $primeiraMissao = $missoes[0];
        $this->assertDatabaseHas('missions', [
            'user_id' => $filho->id,
            'description' => $primeiraMissao['descricao'],
            'coins' => $primeiraMissao['moedas'],
        ]);
    }

    public function test_retorna_zero_quando_nenhuma_missao_eh_informada(): void
    {
        $filho = User::create([
            'name' => 'Marina',
            'email' => 'marina@exemplo.com',
            'role' => UserRole::FILHA,
            'balance' => 0,
        ]);

        $servico = new MissaoSugeridaService();
        $quantidadeCriada = $servico->vincularMissoesAoUsuario($filho, []);

        $this->assertSame(0, $quantidadeCriada);
        $this->assertDatabaseCount('missions', 0);
    }
}
