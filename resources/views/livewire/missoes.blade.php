<?php

use App\Models\Mission;
use App\Enums\PlaylistMissao;
use App\Services\MissaoSugeridaService;
use App\Actions\ResolverFilhoSelecionado;
use function Livewire\Volt\{state, mount, layout};

layout('layouts.app');

state([
    'filho' => null,
    'ehResponsavel' => false,
    'precisaSelecionar' => false,
    'exibirSecaoNovaMissao' => false,
    'descricaoNovaTarefa' => '',
    'moedasNovaTarefa' => '',
    'diaNovaTarefa' => '',
    'playlistSelecionada' => PlaylistMissao::EXPLORADORES_ROTINA->value,
    'missoesSugeridasSelecionadas' => [],
]);

mount(function (ResolverFilhoSelecionado $resolver) {
    if (!auth()->check()) {
        return;
    }

    $resultado = $resolver->executar(request()->query('filho'));
    $this->filho = $resultado['filho'];
    $this->ehResponsavel = $resultado['ehResponsavel'];
    $this->precisaSelecionar = $resultado['precisaSelecionar'];

    if ($this->filho) {
        $idade = $this->filho->obterIdade();
        $playlistPadrao = PlaylistMissao::porIdade($idade);
        $this->playlistSelecionada = $playlistPadrao->value;

        // Pré-seleciona as missões da playlist padrão recomendada por idade.
        $chaves = [];
        foreach ($playlistPadrao->missoes() as $indice => $missao) {
            $chaves[] = "{$playlistPadrao->value}_{$indice}";
        }
        $this->missoesSugeridasSelecionadas = $chaves;
    }
});

$adicionarTarefaRapida = function () {
    if (!$this->filho) return;
    if (!$this->descricaoNovaTarefa || !$this->moedasNovaTarefa) return;

    Mission::create([
        'description' => $this->descricaoNovaTarefa,
        'coins' => (int) $this->moedasNovaTarefa,
        'day' => $this->diaNovaTarefa ?: null,
        'user_id' => $this->filho->id,
    ]);

    $this->descricaoNovaTarefa = '';
    $this->moedasNovaTarefa = '';
    $this->diaNovaTarefa = '';

    \Flux::toast(
        heading: 'Missão Adicionada! 📝',
        text: "Nova missão criada para {$this->filho->name}.",
        variant: 'success'
    );
};

$alternarExpansaoPlaylist = function (string $valorPlaylist) {
    if ($this->playlistSelecionada === $valorPlaylist) {
        $this->playlistSelecionada = null;
    } else {
        $this->playlistSelecionada = $valorPlaylist;
    }
};

$alternarSelecaoMissao = function (string $chaveMissao) {
    if (in_array($chaveMissao, $this->missoesSugeridasSelecionadas)) {
        $this->missoesSugeridasSelecionadas = array_values(array_diff($this->missoesSugeridasSelecionadas, [$chaveMissao]));
    } else {
        $this->missoesSugeridasSelecionadas[] = $chaveMissao;
    }
};

$alternarTodasMissoesDaPlaylist = function (string $valorPlaylist) {
    $playlist = PlaylistMissao::tryFrom($valorPlaylist);
    if (!$playlist) return;

    $totalMissoes = count($playlist->missoes());
    $chavesDesta = [];
    for ($i = 0; $i < $totalMissoes; $i++) {
        $chavesDesta[] = "{$valorPlaylist}_{$i}";
    }

    $todasJaMarcadas = count(array_intersect($chavesDesta, $this->missoesSugeridasSelecionadas)) === $totalMissoes;

    if ($todasJaMarcadas) {
        $this->missoesSugeridasSelecionadas = array_values(array_diff($this->missoesSugeridasSelecionadas, $chavesDesta));
    } else {
        $this->missoesSugeridasSelecionadas = array_values(array_unique(array_merge($this->missoesSugeridasSelecionadas, $chavesDesta)));
    }
};

$adicionarMissoesSugeridas = function (MissaoSugeridaService $servicoMissoes) {
    if (!$this->filho || !in_array($this->filho->role->value, ['S', 'D'])) {
        \Flux::toast(
            heading: 'Atenção ⚠️',
            text: 'Selecione um filho ou filha no painel para adicionar as missões.',
            variant: 'warning'
        );
        return;
    }

    $missoesParaAdicionar = [];
    foreach (PlaylistMissao::cases() as $pl) {
        $todasMissoes = $pl->missoes();
        foreach ($todasMissoes as $idx => $missao) {
            $chave = "{$pl->value}_{$idx}";
            if (in_array($chave, $this->missoesSugeridasSelecionadas)) {
                $missoesParaAdicionar[] = $missao;
            }
        }
    }

    if (empty($missoesParaAdicionar)) {
        \Flux::toast(
            heading: 'Nenhuma missão selecionada',
            text: 'Selecione ao menos uma missão para adicionar à rotina.',
            variant: 'warning'
        );
        return;
    }

    $quantidadeCriada = $servicoMissoes->vincularMissoesAoUsuario($this->filho, $missoesParaAdicionar);

    \Flux::toast(
        heading: 'Playlist Adicionada! 🎵',
        text: "{$quantidadeCriada} missões foram adicionadas para {$this->filho->name}.",
        variant: 'success'
    );
};

?>

<div class="w-full">
    <!-- Header -->
    <div class="header">
        <a href="{{ route('dashboard', $filho ? ['filho' => $filho->id] : []) }}" wire:navigate style="text-decoration: none; display: flex; align-items: center; gap: 8px; color: var(--text-primary);" aria-label="Voltar ao painel">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            <span class="header-logo">Novas Missões 🎯</span>
        </a>
        @if($filho)
            <div class="glass-card" style="margin: 0; padding: 6px 14px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 0.9rem; font-weight: 600;" aria-label="Saldo atual de {{ $filho->name }}">🪙 {{ $filho->balance }}</span>
            </div>
        @endif
    </div>

    @if($precisaSelecionar)
        <!-- Responsável sem filho selecionado -->
        <div style="padding: 0 15px;">
            <div class="glass-card" role="status" style="text-align: center; color: var(--text-secondary); margin: 15px 0; padding: 24px 16px;">
                <span style="font-size: 2.2rem; display: block; margin-bottom: 8px;" aria-hidden="true">👆</span>
                <p style="font-size: 0.95rem; color: #f8fafc; margin: 0 0 14px 0;">
                    Selecione um filho no painel
                </p>
                <a href="{{ route('dashboard') }}" wire:navigate class="btn-primary" style="text-decoration: none; display: inline-block; font-size: 0.9rem; padding: 10px 20px;" aria-label="Voltar ao painel para selecionar um filho">
                    Voltar ao painel
                </a>
            </div>
        </div>
    @elseif($filho)
        <!-- Criação avulsa de missão (Pais) -->
        @if(auth()->check() && in_array(auth()->user()->role->value, ['P', 'M']))
            <div style="padding: 0 15px; margin-top: 15px; margin-bottom: 15px;">
                <div class="glass-card" style="margin: 0;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px; color: #c084fc;">Nova missão para {{ $filho->name }}</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 12px;">
                        Adicione uma nova tarefa rápida para notificar imediatamente as crianças com aviso em texto e sinal sonoro!
                    </p>
                    <form wire:submit.prevent="adicionarTarefaRapida" style="display: flex; flex-direction: column; gap: 10px;">
                        <label for="descricao-nova-tarefa" class="sr-only">Descrição da missão</label>
                        <input id="descricao-nova-tarefa" type="text" wire:model="descricaoNovaTarefa" placeholder="Ex: Escovar os dentes" style="background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 10px; color: #fff; font-family: inherit;" required>
                        <label for="moedas-nova-tarefa" class="sr-only">Quantas moedas a missão vale</label>
                        <input id="moedas-nova-tarefa" type="number" wire:model="moedasNovaTarefa" placeholder="Quantas moedas vale? Ex: 10" style="background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 10px; color: #fff; font-family: inherit;" required>
                        <label for="dia-nova-tarefa" class="sr-only">Dia específico da missão</label>
                        <select id="dia-nova-tarefa" wire:model="diaNovaTarefa" onchange="this.style.color = this.value === '' ? '#9ca3af' : '#fff'" style="background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 10px; color: {{ empty($diaNovaTarefa) ? '#9ca3af' : '#fff' }}; font-family: inherit; outline: none; cursor: pointer;">
                            <option value="" style="color: #9ca3af;" disabled selected hidden>Um dia específico?</option>
                            <option value="Segunda" style="color: #000;">Segunda</option>
                            <option value="Terça" style="color: #000;">Terça</option>
                            <option value="Quarta" style="color: #000;">Quarta</option>
                            <option value="Quinta" style="color: #000;">Quinta</option>
                            <option value="Sexta" style="color: #000;">Sexta</option>
                            <option value="Sábado" style="color: #000;">Sábado</option>
                            <option value="Domingo" style="color: #000;">Domingo</option>
                        </select>
                        <button type="submit" class="btn-primary" style="background: #a855f7;">Salvar</button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Playlists de Missões Sugeridas (conteúdo da página) -->
        <div style="padding: 0 15px; margin-bottom: 15px;">
            @php
                $playlistAtiva = PlaylistMissao::tryFrom($playlistSelecionada) ?? PlaylistMissao::EXPLORADORES_ROTINA;
                $todasPlaylists = PlaylistMissao::cases();
                $idadeFilho = $filho?->obterIdade();
                $basesTerapeuticas = PlaylistMissao::basesTerapeuticas();
            @endphp

            <div class="glass-card" style="margin: 0; border: 2px solid #a855f7; display: flex; flex-direction: column; gap: 14px;" aria-labelledby="tituloPlaylists">
                <!-- Cabeçalho -->
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.4rem;" aria-hidden="true">🎧</span>
                        <h3 id="tituloPlaylists" style="font-size: 1.2rem; font-weight: 800; color: #c084fc; margin: 0;">Playlists de Missões</h3>
                    </div>
                    <p style="font-size: 0.82rem; color: #cbd5e1; margin-top: 4px;">
                        @if($filho && in_array($filho->role->value, ['S', 'D']))
                            Sugestões para <strong>{{ $filho->name }}</strong>
                            @if($idadeFilho !== null) ({{ $idadeFilho }} anos) @endif
                        @else
                            Toque em uma playlist para ver e selecionar as tarefas:
                        @endif
                    </p>
                </div>

                <!-- Destaque das Bases Terapêuticas e de Desenvolvimento -->
                <div style="background: rgba(168, 85, 247, 0.12); border: 1px solid rgba(168, 85, 247, 0.35); border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.15rem;" aria-hidden="true">🌱</span>
                        <h4 style="font-size: 0.85rem; font-weight: 700; color: #d8b4fe; margin: 0;">
                            Fundamentação Terapêutica & Desenvolvimento
                        </h4>
                    </div>
                    <p style="font-size: 0.76rem; color: #f1f5f9; line-height: 1.45; margin: 0;">
                        As atividades e faixas etárias são fundamentadas em referenciais clínicos e pedagógicos de desenvolvimento infantil e autonomia:
                    </p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: 2px;">
                        @foreach($basesTerapeuticas as $base)
                            <div style="background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(168, 85, 247, 0.25); border-radius: 8px; padding: 6px 8px;">
                                <strong style="display: block; font-size: 0.72rem; color: #c084fc;">{{ $base['sigla'] }}</strong>
                                <span style="font-size: 0.68rem; color: #cbd5e1; line-height: 1.3; display: block;">{{ $base['descricao'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Lista de Playlists em Accordion Expansível -->
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @foreach($todasPlaylists as $pl)
                        @php
                            $estaExpandida = $playlistSelecionada === $pl->value;
                            $missoesDaPlaylist = $pl->missoes();
                            $totalMissoes = count($missoesDaPlaylist);

                            $selecionadasDestaPlaylist = 0;
                            foreach(range(0, $totalMissoes - 1) as $idx) {
                                if (in_array("{$pl->value}_{$idx}", $missoesSugeridasSelecionadas)) {
                                    $selecionadasDestaPlaylist++;
                                }
                            }
                            $todasDestaMarcadas = $selecionadasDestaPlaylist === $totalMissoes;
                        @endphp

                        <div style="background: {{ $estaExpandida ? 'rgba(168, 85, 247, 0.12)' : 'rgba(0, 0, 0, 0.25)' }}; border: 1px solid {{ $estaExpandida ? '#a855f7' : 'var(--card-border)' }}; border-radius: 14px; overflow: hidden; transition: all 0.2s;">

                            <!-- Cabeçalho Clicável da Playlist -->
                            <button type="button"
                                    wire:click="alternarExpansaoPlaylist('{{ $pl->value }}')"
                                    aria-expanded="{{ $estaExpandida ? 'true' : 'false' }}"
                                    aria-label="{{ $estaExpandida ? 'Recolher' : 'Expandir' }} a playlist {{ $pl->titulo() }}"
                                    style="width: 100%; background: none; border: none; text-align: left; padding: 12px 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 10px; user-select: none; color: inherit; font-family: inherit;">
                                <span style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 1.3rem;" aria-hidden="true">{{ $pl->icone() }}</span>
                                    <span>
                                        <span style="display: block; font-size: 0.92rem; font-weight: 700; color: {{ $estaExpandida ? '#c084fc' : '#f8fafc' }};">
                                            {{ $pl->titulo() }}
                                        </span>
                                        <span style="display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                                            <span style="font-size: 0.72rem; color: #cbd5e1; background: rgba(255,255,255,0.08); padding: 1px 6px; border-radius: 4px;">
                                                {{ $pl->faixaEtaria() }}
                                            </span>
                                            <span style="font-size: 0.72rem; color: #cbd5e1;">
                                                {{ $totalMissoes }} tarefas
                                            </span>
                                            @if($selecionadasDestaPlaylist > 0)
                                                <span style="font-size: 0.72rem; color: #d8b4fe; font-weight: 600; background: rgba(168, 85, 247, 0.25); padding: 1px 6px; border-radius: 4px;">
                                                    {{ $selecionadasDestaPlaylist }} marcadas
                                                </span>
                                            @endif
                                        </span>
                                    </span>
                                </span>

                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <span style="color: #cbd5e1; font-size: 0.75rem; transition: transform 0.2s; display: inline-block; transform: {{ $estaExpandida ? 'rotate(180deg)' : 'rotate(0deg)' }};" aria-hidden="true">
                                        ▼
                                    </span>
                                </span>
                            </button>

                            <!-- Tarefas da Playlist (Expandidas Abaixo Dela) -->
                            @if($estaExpandida)
                                <div style="padding: 0 14px 14px 14px; border-top: 1px solid rgba(255, 255, 255, 0.06); margin-top: 4px; padding-top: 12px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 8px;">
                                        <p style="font-size: 0.78rem; color: #cbd5e1; margin: 0; line-height: 1.35;">
                                            {{ $pl->descricao() }}
                                        </p>
                                        <button type="button"
                                                wire:click="alternarTodasMissoesDaPlaylist('{{ $pl->value }}')"
                                                style="background: none; border: none; color: #c084fc; font-size: 0.78rem; font-weight: 600; cursor: pointer; white-space: nowrap; padding-left: 6px;"
                                                aria-label="{{ $todasDestaMarcadas ? 'Desmarcar todas as tarefas desta playlist' : 'Marcar todas as tarefas desta playlist' }}">
                                            {{ $todasDestaMarcadas ? 'Desmarcar' : 'Marcar todas' }}
                                        </button>
                                    </div>

                                    <!-- Base Terapêutica Específica da Playlist -->
                                    <div style="background: rgba(168, 85, 247, 0.1); border-left: 3px solid #a855f7; border-radius: 6px; padding: 6px 10px; margin-bottom: 10px;">
                                        <span style="font-size: 0.72rem; color: #e2e8f0; line-height: 1.35; display: block;">
                                            <strong style="color: #c084fc;">Base Terapêutica:</strong> {{ $pl->baseTerapeutica() }}
                                        </span>
                                    </div>

                                    <!-- Lista de Tarefas Inline -->
                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        @foreach($missoesDaPlaylist as $indice => $missao)
                                            @php
                                                $chave = "{$pl->value}_{$indice}";
                                                $marcada = in_array($chave, $missoesSugeridasSelecionadas);
                                                $idCheckbox = "missao-{$pl->value}-{$indice}";
                                            @endphp
                                            <label for="{{ $idCheckbox }}"
                                                   style="background: {{ $marcada ? 'rgba(168, 85, 247, 0.18)' : 'rgba(0, 0, 0, 0.2)' }}; border: 1px solid {{ $marcada ? '#a855f7' : 'var(--card-border)' }}; border-radius: 10px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; gap: 10px; cursor: pointer; transition: all 0.15s;">
                                                <span style="display: flex; align-items: center; gap: 10px;">
                                                    <input type="checkbox"
                                                           id="{{ $idCheckbox }}"
                                                           wire:click="alternarSelecaoMissao('{{ $chave }}')"
                                                           {{ $marcada ? 'checked' : '' }}
                                                           style="cursor: pointer; accent-color: #a855f7; width: 16px; height: 16px;" />
                                                    <span style="font-size: 0.85rem; font-weight: 500; color: {{ $marcada ? '#f8fafc' : 'var(--text-secondary)' }};">
                                                        {{ $missao['descricao'] }}
                                                    </span>
                                                </span>
                                                <span style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                                    @if(!empty($missao['dia']))
                                                        <span style="font-size: 0.72rem; color: var(--text-secondary); background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 6px;">
                                                            📅 {{ $missao['dia'] }}
                                                        </span>
                                                    @endif
                                                    <span style="font-size: 0.82rem; color: #818cf8; font-weight: 600; background: rgba(129, 140, 248, 0.15); padding: 2px 8px; border-radius: 8px;">
                                                        🪙 +{{ $missao['moedas'] }}
                                                    </span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>

                <!-- Rodapé com Ação -->
                <div style="display: flex; gap: 10px; border-top: 1px solid var(--card-border); padding-top: 10px;">
                    <button type="button"
                            wire:click="adicionarMissoesSugeridas"
                            class="btn-primary"
                            style="flex: 1; background: #a855f7; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.35); font-size: 0.85rem; padding: 10px; opacity: {{ count($missoesSugeridasSelecionadas) === 0 ? '0.5' : '1' }};"
                            aria-label="Adicionar as missões selecionadas à rotina"
                            {{ count($missoesSugeridasSelecionadas) === 0 ? 'disabled' : '' }}>
                        ➕ Adicionar à Rotina ({{ count($missoesSugeridasSelecionadas) }})
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
