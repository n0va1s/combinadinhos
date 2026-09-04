<?php

namespace Tests\Unit;

use App\Enums\PlaylistMissao;
use PHPUnit\Framework\TestCase;

class PlaylistMissaoTest extends TestCase
{
    public function test_todos_os_casos_possuem_titulo_icone_e_missoes_validas(): void
    {
        foreach (PlaylistMissao::cases() as $playlist) {
            $this->assertNotEmpty($playlist->titulo());
            $this->assertNotEmpty($playlist->icone());
            $this->assertNotEmpty($playlist->descricao());
            
            $missoes = $playlist->missoes();
            $this->assertIsArray($missoes);
            $this->assertNotEmpty($missoes);

            foreach ($missoes as $missao) {
                $this->assertArrayHasKey('descricao', $missao);
                $this->assertArrayHasKey('moedas', $missao);
                $this->assertNotEmpty($missao['descricao']);
                $this->assertGreaterThan(0, $missao['moedas']);
            }
        }
    }

    public function test_resolve_playlist_correta_por_idade(): void
    {
        $this->assertSame(PlaylistMissao::PEQUENOS_CONQUISTADORES, PlaylistMissao::porIdade(3));
        $this->assertSame(PlaylistMissao::PEQUENOS_CONQUISTADORES, PlaylistMissao::porIdade(5));
        $this->assertSame(PlaylistMissao::EXPLORADORES_ROTINA, PlaylistMissao::porIdade(6));
        $this->assertSame(PlaylistMissao::EXPLORADORES_ROTINA, PlaylistMissao::porIdade(8));
        $this->assertSame(PlaylistMissao::MESTRES_AUTONOMIA, PlaylistMissao::porIdade(9));
        $this->assertSame(PlaylistMissao::MESTRES_AUTONOMIA, PlaylistMissao::porIdade(12));
        $this->assertSame(PlaylistMissao::JOVENS_PROTAGONISTAS, PlaylistMissao::porIdade(13));
        $this->assertSame(PlaylistMissao::JOVENS_PROTAGONISTAS, PlaylistMissao::porIdade(16));

        // Padrão quando idade é nula
        $this->assertSame(PlaylistMissao::EXPLORADORES_ROTINA, PlaylistMissao::porIdade(null));
    }
}
