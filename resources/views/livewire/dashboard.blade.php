<?php

use App\Models\User;
use App\Models\Mission;
use App\Models\Transaction;
use App\Models\Reward;
use App\Enums\TransactionStatus;
use App\Enums\PlaylistMissao;
use App\Services\MissaoSugeridaService;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount, layout};

layout('layouts.app');

state([
    'usuarios' => [],
    'idUsuarioSelecionado' => null,
    'usuarioSelecionado' => null,
    'missoes' => [],
    'somVerificado' => false,
    'hashUltimasMissoes' => '',
    'transacoesPendentes' => [],
    'exibirSecaoNovaMissao' => false,
    'descricaoNovaTarefa' => '',
    'moedasNovaTarefa' => '',
    'diaNovaTarefa' => '',
    'exibirModalRecompensas' => false,
    'recompensas' => [],
    'descricaoNovaRecompensa' => '',
    'custoNovaRecompensa' => '',
    'exibirModalPlaylists' => false,
    'playlistSelecionada' => PlaylistMissao::EXPLORADORES_ROTINA->value,
    'missoesSugeridasSelecionadas' => [],
]);

mount(function () {
    if (auth()->check()) {
        $usuario = auth()->user();
        $this->usuarioSelecionado = $usuario;
        $this->idUsuarioSelecionado = $usuario->id;
    }
    $this->carregarUsuarios();
    $this->carregarMissoes(silencioso: true);
    $this->carregarTransacoesPendentes();
    $this->carregarRecompensas();
});

$carregarUsuarios = function () {
    // Se o usuário selecionado estiver vinculado a uma família, carregamos apenas os membros daquela família.
    if ($this->usuarioSelecionado && $this->usuarioSelecionado->family_id) {
        $this->usuarios = User::where('family_id', $this->usuarioSelecionado->family_id)->get();
    } else {
        $this->usuarios = User::all();
    }

    if ($this->idUsuarioSelecionado) {
        $this->usuarioSelecionado = User::find($this->idUsuarioSelecionado);
    }
};

$carregarTransacoesPendentes = function () {
    if (auth()->check() && in_array(auth()->user()->role->value, ['P', 'M'])) {
        $idsUsuariosFamilia = User::where('family_id', auth()->user()->family_id)->pluck('id');
        $this->transacoesPendentes = Transaction::whereIn('user_id', $idsUsuariosFamilia)
            ->where('status', TransactionStatus::PENDENTE)
            ->orderBy('created_at', 'desc')
            ->get();
    } else {
        $this->transacoesPendentes = [];
    }
};

$carregarMissoes = function (bool $silencioso = false) {
    // Array para tradução do dia atual do Carbon/PHP para português
    $diasTraduzidos = [
        'Sunday' => 'Domingo',
        'Monday' => 'Segunda',
        'Tuesday' => 'Terça',
        'Wednesday' => 'Quarta',
        'Thursday' => 'Quinta',
        'Friday' => 'Sexta',
        'Saturday' => 'Sábado',
    ];
    $diaAtualPt = $diasTraduzidos[\Carbon\Carbon::now()->format('l')];

    $consulta = Mission::query();

    if ($this->usuarioSelecionado) {
        $consulta->where('user_id', $this->usuarioSelecionado->id);
    }

    $this->missoes = $consulta->where(function ($q) use ($diaAtualPt) {
        $q->where('day', $diaAtualPt)
          ->orWhereNull('day')
          ->orWhere('day', '')
          ->orWhere('day', 'Hoje');
    })->orderBy('description', 'asc')->get();
    
    $novoHash = md5(json_encode($this->missoes->pluck('id', 'updated_at')->toArray()));
    
    if (!$silencioso && $this->hashUltimasMissoes && $this->hashUltimasMissoes !== $novoHash) {
        $listaDescricoes = $this->missoes->pluck('description')->implode(', ');
        \Flux::toast(
            heading: 'Tarefas Atualizadas! 📝',
            text: 'Novas tarefas disponíveis: ' . $listaDescricoes,
            variant: 'info'
        );
    }
    $this->hashUltimasMissoes = $novoHash;
};

$selecionarUsuario = function ($id) {
    $this->idUsuarioSelecionado = $id;
    $this->usuarioSelecionado = User::find($id);
    $this->carregarUsuarios();
    $this->carregarMissoes(silencioso: true);
    $this->carregarRecompensas();
};

$adicionarTarefaRapida = function () {
    if (!$this->usuarioSelecionado) return;
    if (!$this->descricaoNovaTarefa || !$this->moedasNovaTarefa) return;

    Mission::create([
        'description' => $this->descricaoNovaTarefa,
        'coins' => (int) $this->moedasNovaTarefa,
        'day' => $this->diaNovaTarefa ?: null,
        'user_id' => $this->usuarioSelecionado->id,
    ]);
    
    $this->descricaoNovaTarefa = '';
    $this->moedasNovaTarefa = '';
    $this->diaNovaTarefa = '';
    $this->carregarMissoes();
};

$carregarRecompensas = function () {
    if ($this->usuarioSelecionado) {
        $this->recompensas = Reward::where('user_id', $this->usuarioSelecionado->id)->orderBy('cost', 'asc')->get();
    } else {
        $this->recompensas = [];
    }
};

$adicionarRecompensa = function () {
    if (!$this->usuarioSelecionado) return;
    if (!$this->descricaoNovaRecompensa || !$this->custoNovaRecompensa) return;

    Reward::create([
        'description' => $this->descricaoNovaRecompensa,
        'cost' => (int) $this->custoNovaRecompensa,
        'user_id' => $this->usuarioSelecionado->id,
    ]);
    
    $this->descricaoNovaRecompensa = '';
    $this->custoNovaRecompensa = '';
    $this->carregarRecompensas();
    
    \Flux::toast(
        heading: 'Recompensa Adicionada! 🎁',
        text: 'Nova recompensa disponível para resgate.',
        variant: 'success'
    );
};

$resgatarRecompensa = function ($idRecompensa) {
    if (!$this->usuarioSelecionado) return;
    
    $recompensa = Reward::find($idRecompensa);
    if (!$recompensa) return;

    $usuarioLogado = auth()->user();
    $ehResponsavel = $usuarioLogado && in_array($usuarioLogado->role->value, ['P', 'M']);
    
    if ($ehResponsavel) {
        $this->usuarioSelecionado->balance -= $recompensa->cost;
        $this->usuarioSelecionado->save();

        Transaction::create([
            'action' => 'Resgatou',
            'user_name' => $this->usuarioSelecionado->name,
            'detail' => 'Resgatou recompensa: ' . $recompensa->description,
            'amount' => -$recompensa->cost,
            'status' => TransactionStatus::APROVADO,
            'user_id' => $this->usuarioSelecionado->id,
        ]);

        $this->carregarUsuarios();
        
        \Flux::toast(
            heading: 'Recompensa Resgatada! 🎉',
            text: "{$this->usuarioSelecionado->name} resgatou: {$recompensa->description}",
            variant: 'success'
        );
        
        $this->dispatch('play-task-alert', ['sound' => 'applause']);
    } else {
        if ($this->usuarioSelecionado->balance < $recompensa->cost) {
            \Flux::toast(
                heading: 'Saldo Insuficiente 😢',
                text: "Você precisa de mais " . ($recompensa->cost - $this->usuarioSelecionado->balance) . " moedas.",
                variant: 'danger'
            );
            return;
        }

        Transaction::create([
            'action' => 'Resgate',
            'user_name' => $this->usuarioSelecionado->name,
            'detail' => 'Aguardando Aprovação (Resgate): ' . $recompensa->description,
            'amount' => -$recompensa->cost,
            'status' => TransactionStatus::PENDENTE,
            'user_id' => $this->usuarioSelecionado->id,
        ]);

        \Flux::toast(
            heading: 'Enviado para Aprovação! ⏳',
            text: "Seu pedido de resgate foi enviado.",
            variant: 'info'
        );
    }
};

$excluirRecompensa = function ($idRecompensa) {
    $recompensa = Reward::find($idRecompensa);
    if ($recompensa) {
        $recompensa->delete();
        $this->carregarRecompensas();
    }
};

$marcarComoConcluido = function ($idMissao) {
    if (!$this->usuarioSelecionado) {
        return;
    }

    $missao = Mission::find($idMissao);
    if (!$missao) return;

    $usuarioLogado = auth()->user();
    $ehResponsavel = $usuarioLogado && in_array($usuarioLogado->role->value, ['P', 'M']);
    $ehNegativo = $missao->coins < 0;

    if ($ehResponsavel && $this->usuarioSelecionado->id === $usuarioLogado->id) {
        \Flux::toast(
            heading: 'Atenção ⚠️',
            text: 'Selecione um filho no painel acima para registrar a tarefa.',
            variant: 'warning'
        );
        return;
    }

    if ($ehResponsavel) {
        // Pais aprovam direto.
        $this->usuarioSelecionado->balance += $missao->coins;
        $this->usuarioSelecionado->save();

        Transaction::create([
            'action' => $ehNegativo ? 'Perdeu' : 'Ganhou',
            'user_name' => $this->usuarioSelecionado->name,
            'detail' => 'Realizou: ' . $missao->description,
            'amount' => $missao->coins,
            'status' => TransactionStatus::APROVADO,
            'user_id' => $this->usuarioSelecionado->id,
        ]);

        $this->carregarUsuarios();

        $titulo = $ehNegativo ? 'Continue se esforçando! 💪' : 'Parabéns! 🎉';
        $mensagem = $ehNegativo 
            ? "{$this->usuarioSelecionado->name} perdeu " . abs($missao->coins) . " moedas: {$missao->description}" 
            : "{$this->usuarioSelecionado->name} ganhou {$missao->coins} moedas!";

        \Flux::toast(
            heading: $titulo,
            text: $mensagem,
            variant: $ehNegativo ? 'danger' : 'success'
        );

        $this->dispatch('play-task-alert', [
            'sound' => $ehNegativo ? 'suspense' : 'applause'
        ]);
    } else {
        // Filhos enviando para aprovação (mesmo as negativas)
        Transaction::create([
            'action' => $ehNegativo ? 'Perdeu' : 'Ganhou',
            'user_name' => $this->usuarioSelecionado->name,
            'detail' => 'Aguardando Aprovação: ' . $missao->description,
            'amount' => $missao->coins,
            'status' => TransactionStatus::PENDENTE,
            'user_id' => $this->usuarioSelecionado->id,
        ]);

        \Flux::toast(
            heading: 'Enviado para Aprovação! ⏳',
            text: "A tarefa '{$missao->description}' foi enviada para aprovação.",
            variant: 'info'
        );
    }
};

$aprovarTransacao = function ($idTransacao) {
    $transacao = Transaction::find($idTransacao);
    if ($transacao && $transacao->status === TransactionStatus::PENDENTE) {
        $transacao->status = TransactionStatus::APROVADO;
        $ehRecompensa = $transacao->action === 'Resgate';
        
        if ($ehRecompensa) {
            $transacao->action = 'Resgatou';
            $transacao->detail = str_replace('Aguardando Aprovação (Resgate): ', 'Resgatou recompensa: ', $transacao->detail);
        } else {
            $transacao->detail = str_replace('Aguardando Aprovação: ', 'Realizou: ', $transacao->detail);
        }
        $transacao->save();
        
        $usuario = User::find($transacao->user_id);
        if ($usuario) {
            $usuario->balance += $transacao->amount;
            $usuario->save();
        }
        $this->carregarTransacoesPendentes();
        $this->carregarUsuarios();
        
        $ehNegativo = $transacao->amount < 0;
        $ehPunicao = $ehNegativo && !$ehRecompensa;

        $tituloToast = 'Aprovado! ✅';
        $textoToast = "Você aprovou a tarefa para {$usuario->name}.";
        $varianteToast = 'success';
        $somToast = 'applause';

        if ($ehPunicao) {
            $tituloToast = 'Punição Aplicada ⚖️';
            $textoToast = "Você aplicou a punição para {$usuario->name}.";
            $varianteToast = 'danger';
            $somToast = 'suspense';
        } elseif ($ehRecompensa) {
            $tituloToast = 'Recompensa Aprovada! 🎉';
            $textoToast = "Você aprovou o resgate para {$usuario->name}.";
        }

        \Flux::toast(
            heading: $tituloToast,
            text: $textoToast,
            variant: $varianteToast
        );
        
        $this->dispatch('play-task-alert', [
            'sound' => $somToast
        ]);
    }
};

$recusarTransacao = function ($idTransacao) {
    $transacao = Transaction::find($idTransacao);
    if ($transacao && $transacao->status === TransactionStatus::PENDENTE) {
        $transacao->status = TransactionStatus::REJEITADO;
        $transacao->save();
        $this->carregarTransacoesPendentes();
        
        \Flux::toast(
            heading: 'Recusado ❌',
            text: 'A missão foi recusada.',
            variant: 'danger'
        );
    }
};

$excluirMissao = function ($idMissao) {
    $missao = Mission::find($idMissao);
    if ($missao) {
        $missao->delete();
        $this->carregarMissoes();
    }
};

$abrirModalPlaylists = function () {
    $idade = $this->usuarioSelecionado?->obterIdade();
    $playlistPadrao = PlaylistMissao::porIdade($idade);

    $this->playlistSelecionada = $playlistPadrao->value;
    
    // Pré-seleciona as missões da playlist padrão recomendada
    $chaves = [];
    foreach ($playlistPadrao->missoes() as $indice => $missao) {
        $chaves[] = "{$playlistPadrao->value}_{$indice}";
    }
    $this->missoesSugeridasSelecionadas = $chaves;
    $this->exibirModalPlaylists = true;
};

$fecharModalPlaylists = function () {
    $this->exibirModalPlaylists = false;
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
    if (!$this->usuarioSelecionado || !in_array($this->usuarioSelecionado->role->value, ['S', 'D'])) {
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

    $quantidadeCriada = $servicoMissoes->vincularMissoesAoUsuario($this->usuarioSelecionado, $missoesParaAdicionar);

    $this->exibirModalPlaylists = false;
    $this->carregarMissoes();

    \Flux::toast(
        heading: 'Playlist Adicionada! 🎵',
        text: "{$quantidadeCriada} missões foram adicionadas para {$this->usuarioSelecionado->name}.",
        variant: 'success'
    );
};

$sair = function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    $this->redirect('/', navigate: true);
};

?>

<div class="w-full">
    <!-- Header -->
    <div class="header">
        <span class="header-logo">Combinadinhos 🤝</span>
        @if($usuarioSelecionado)
            <div style="display: flex; align-items: center; gap: 8px;">
                <div wire:click="$toggle('exibirModalRecompensas')" class="glass-card" style="margin: 0; padding: 6px 14px; border-radius: 12px; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: transform 0.1s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <span style="font-size: 0.9rem; font-weight: 600;">🪙 {{ $usuarioSelecionado->balance }}</span>
                </div>
                <button wire:click="sair" class="btn-primary" style="background: #ef4444; font-size: 0.85rem; padding: 6px 12px; box-shadow: none;">Sair</button>
            </div>
        @endif
    </div>

    <!-- Bloco de Perfil / Login -->
    <div class="glass-card" style="margin-top: 5px;">
        @if($usuarioSelecionado)
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                <h3 style="font-size: 1.2rem; font-weight: 700; margin: 0;">
                    @if(auth()->check() && auth()->user()->id !== $usuarioSelecionado->id)
                        Registrando para {{ $usuarioSelecionado->name }} 👦
                    @else
                        Olá, {{ $usuarioSelecionado->name }}!
                    @endif
                </h3>
                @if(auth()->check() && in_array(auth()->user()->role->value, ['P', 'M']))
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" 
                                wire:click="abrirModalPlaylists" 
                                title="Playlists Sugeridas de Missões"
                                style="background: rgba(168, 85, 247, 0.15); border: 1px solid #a855f7; border-radius: 8px; padding: 6px 12px; display: flex; align-items: center; gap: 6px; cursor: pointer; color: #c084fc; font-size: 0.85rem; font-weight: 600; transition: all 0.2s;">
                            <span>💡</span> Playlists
                        </button>
                        <button wire:click="$toggle('exibirSecaoNovaMissao')" 
                                title="{{ $exibirSecaoNovaMissao ? 'Ocultar Cadastro' : 'Cadastrar Missão' }}"
                                style="background: {{ $exibirSecaoNovaMissao ? 'rgba(168, 85, 247, 0.2)' : 'rgba(255,255,255,0.05)' }}; border: 1px solid {{ $exibirSecaoNovaMissao ? '#a855f7' : 'var(--card-border)' }}; border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="{{ $exibirSecaoNovaMissao ? '#c084fc' : '#fff' }}" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
            
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 8px;">
                Seu perfil é de <strong>{{ auth()->user()->role->label() }}</strong>
            </p>
            <p style="font-size: 0.85rem; color: #818cf8; font-weight: 600;">
                Marque as missões realizadas hoje (positivas ou negativas)
            </p>
            


            @if(auth()->check() && in_array(auth()->user()->role->value, ['P', 'M']))
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--card-border);">
                    <label for="select-filho" style="font-size: 0.85rem; color: var(--text-secondary); display: block; margin-bottom: 8px;">Registrar em nome de:</label>
                    @php
                        $possuiFilhos = false;
                        foreach($usuarios as $usuario) {
                            if (in_array($usuario->role->value, ['S', 'D'])) {
                                $possuiFilhos = true;
                                break;
                            }
                        }
                    @endphp
                    <select id="select-filho" 
                            wire:change="selecionarUsuario($event.target.value)" 
                            style="width: 100%; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--card-border); color: #f8fafc; padding: 10px 12px; border-radius: 12px; font-size: 0.9rem; outline: none; cursor: pointer; transition: border-color 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"
                            {{ !$possuiFilhos ? 'disabled' : '' }}>
                        @if(!$possuiFilhos)
                            <option value="" selected>Nenhum filho cadastrado(a)</option>
                        @else
                            <option value="{{ auth()->user()->id }}" {{ $idUsuarioSelecionado == auth()->user()->id ? 'selected' : '' }}>Selecione um filho...</option>
                            @foreach($usuarios as $usuario)
                                @if(in_array($usuario->role->value, ['S', 'D']))
                                    <option value="{{ $usuario->id }}" {{ $idUsuarioSelecionado == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->role->icon() }} {{ $usuario->name }}
                                    </option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>
            @endif
        @else
            <h3 style="font-size: 1.1rem; margin-bottom: 12px; color: var(--text-secondary);">Identifique-se para começar</h3>
            <div style="display: flex; gap: 10px;">
                <a href="/login" class="btn-primary" style="text-decoration: none; font-size: 0.9rem; padding: 10px 20px;">Acesse</a>
                <a href="/register" class="btn-primary" style="text-decoration: none; font-size: 0.9rem; padding: 10px 20px; background: rgba(255,255,255,0.05); box-shadow: none;">Cadastre-se</a>
            </div>
        @endif
    </div>

    <div wire:poll.10s="carregarMissoes"></div>

    <!-- Aguardando Aprovação (Pais) -->
    @if($usuarioSelecionado && in_array($usuarioSelecionado->role->value, ['P', 'M']) && count($transacoesPendentes) > 0)
        <div style="padding: 0 15px;">
            <h2 style="font-size: 1.3rem; margin: 15px 0 10px; font-weight: 800; color: #fbbf24;">Aguardando Aprovação ⏳</h2>
            @foreach($transacoesPendentes as $transacao)
                <div class="glass-card" style="margin: 0 0 15px 0; display: flex; flex-direction: column; gap: 10px; border-left: 4px solid #fbbf24;">
                    <div>
                        <h4 style="font-size: 1rem; font-weight: 700; color: #f8fafc;">{{ $transacao->user_name }}</h4>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">{{ $transacao->detail }}</p>
                        <span style="display: inline-block; margin-top: 6px; font-size: 0.85rem; color: #fbbf24; font-weight: 600; background: rgba(251, 191, 36, 0.15); padding: 2px 8px; border-radius: 8px;">
                            🪙 {{ $transacao->amount > 0 ? '+' : '' }}{{ $transacao->amount }} moedas
                        </span>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 5px;">
                        <button wire:click="aprovarTransacao('{{ $transacao->id }}')" class="btn-primary" style="flex: 1; padding: 8px; background: var(--success); font-size: 0.9rem;">
                            Aprovar
                        </button>
                        <button wire:click="recusarTransacao('{{ $transacao->id }}')" class="btn-primary" style="flex: 1; padding: 8px; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; font-size: 0.9rem; box-shadow: none;">
                            Recusar
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal / Seção de Recompensas -->
    @if($exibirModalRecompensas)
        <div style="padding: 0 15px; margin-bottom: 15px;">
            <div class="glass-card" style="margin: 0; border: 2px solid #a855f7;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="font-size: 1.2rem; font-weight: 800; color: #c084fc; margin: 0;">Loja de Recompensas 🎁</h3>
                    <button wire:click="$toggle('exibirModalRecompensas')" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.2rem;">&times;</button>
                </div>
                
                @if(count($recompensas) === 0)
                    <p style="font-size: 0.9rem; color: var(--text-secondary); text-align: center; margin-bottom: 15px;">
                        Nenhuma recompensa cadastrada.
                    </p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                        @foreach($recompensas as $recompensa)
                            <div style="background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 10px; padding: 12px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 4px;">{{ $recompensa->description }}</h4>
                                    <span style="font-size: 0.85rem; color: #fbbf24; font-weight: 600;">
                                        🪙 {{ $recompensa->cost }} moedas
                                    </span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    @if(auth()->check() && in_array(auth()->user()->role->value, ['P', 'M']))
                                        <button wire:click="excluirRecompensa('{{ $recompensa->id }}')" style="background: rgba(239, 68, 68, 0.2); border: none; color: #f87171; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer;" title="Excluir Recompensa" onclick="confirm('Excluir recompensa?') || event.stopImmediatePropagation()">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    @endif
                                    <button wire:click="resgatarRecompensa('{{ $recompensa->id }}')" class="btn-primary" style="background: var(--accent); font-size: 0.85rem; padding: 6px 12px; box-shadow: none;">
                                        Resgatar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                @if(auth()->check() && in_array(auth()->user()->role->value, ['P', 'M']))
                    <div style="border-top: 1px solid var(--card-border); padding-top: 15px;">
                        <h4 style="font-size: 0.95rem; margin-bottom: 10px; color: #e2e8f0;">Adicionar Recompensa (Pais)</h4>
                        <form wire:submit.prevent="adicionarRecompensa" style="display: flex; gap: 8px;">
                            <input type="text" wire:model="descricaoNovaRecompensa" placeholder="Ex: Sorvete" style="flex: 2; background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 8px; color: #fff; font-size: 0.85rem;" required>
                            <input type="number" wire:model="custoNovaRecompensa" placeholder="Valor" style="flex: 1; background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 8px; color: #fff; font-size: 0.85rem;" required>
                            <button type="submit" class="btn-primary" style="background: #a855f7; padding: 8px 12px; font-size: 0.85rem; box-shadow: none;">+</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Painel dos Pais (Notificar e Convidar) -->
    @if(auth()->check() && in_array(auth()->user()->role->value, ['P', 'M']) && $exibirSecaoNovaMissao)
        <div style="padding: 0 15px; margin-bottom: 15px;">
            <div class="glass-card" style="margin: 0;">
                <h3 style="font-size: 1.1rem; margin-bottom: 12px; color: #c084fc;">Novas missões</h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 12px;">
                    Adicione uma nova tarefa rápida para notificar imediatamente as crianças com aviso em texto e sinal sonoro!
                </p>
                <div style="background: rgba(168, 85, 247, 0.1); border: 1px dashed rgba(168, 85, 247, 0.5); border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #e9d5ff;">Sem ideias para novas missões?</div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Use uma lista pronta baseada na idade do seu filho.</div>
                    </div>
                    <button type="button" wire:click="abrirModalPlaylists" class="btn-primary" style="background: #a855f7; font-size: 0.8rem; padding: 6px 12px; white-space: nowrap; box-shadow: none;">
                        Ver Playlists 🎵
                    </button>
                </div>
                <form wire:submit.prevent="adicionarTarefaRapida" style="display: flex; flex-direction: column; gap: 10px;">
                    <input type="text" wire:model="descricaoNovaTarefa" placeholder="Ex: Escovar os dentes" style="background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 10px; color: #fff; font-family: inherit;" required>
                    <input type="number" wire:model="moedasNovaTarefa" placeholder="Quantas moedas vale? Ex: 10" style="background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 10px; color: #fff; font-family: inherit;" required>
                    <select wire:model="diaNovaTarefa" onchange="this.style.color = this.value === '' ? '#9ca3af' : '#fff'" style="background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 10px; color: {{ empty($diaNovaTarefa) ? '#9ca3af' : '#fff' }}; font-family: inherit; outline: none; cursor: pointer;">
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

    <!-- Missões do Dia -->
    @if($usuarioSelecionado)
        <div style="padding: 0 15px;">
            <h2 style="font-size: 1.3rem; margin: 15px 0 10px; font-weight: 800;">Missões do Dia</h2>
            
            @if(count($missoes) === 0)
                <div class="glass-card" style="text-align: center; color: var(--text-secondary); margin: 0 0 15px 0; padding: 24px 16px;">
                    @if(auth()->check() && in_array(auth()->user()->role->value, ['P', 'M']) && in_array($usuarioSelecionado->role->value, ['S', 'D']))
                        <span style="font-size: 2.2rem; display: block; margin-bottom: 8px;">📋✨</span>
                        <h4 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px;">Nenhuma missão ainda</h4>
                        <p style="font-size: 0.85rem; color: var(--text-secondary); max-width: 380px; margin: 0 auto 16px;">
                            Que tal uma playlist da nossa comunidade com missões recomendada para a idade de {{ $usuarioSelecionado->name }}?
                        </p>
                        <button type="button" wire:click="abrirModalPlaylists" class="btn-primary" style="background: #a855f7; display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; font-size: 0.9rem;">
                            <span>🎧</span> Explorar Playlists de Missões
                        </button>
                    @else
                        Selecione seu filho ou filha
                    @endif
                </div>
            @else
                @foreach($missoes as $missao)
                    <div class="glass-card" style="margin: 0 0 15px 0; display: flex; justify-content: space-between; align-items: center; gap: 15px; border-left: 4px solid {{ $missao->coins < 0 ? '#ef4444' : 'transparent' }};">
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 4px;">{{ $missao->description }}</h4>
                            @if($missao->coins < 0)
                                <span style="font-size: 0.85rem; color: #f87171; font-weight: 600; background: rgba(239, 68, 68, 0.15); padding: 2px 8px; border-radius: 8px;">
                                    🪙 {{ $missao->coins }} moedas
                                </span>
                            @else
                                <span style="font-size: 0.85rem; color: #818cf8; font-weight: 600; background: rgba(129, 140, 248, 0.15); padding: 2px 8px; border-radius: 8px;">
                                    🪙 +{{ $missao->coins }} moedas
                                </span>
                            @endif
                            @if($missao->day)
                                <span style="font-size: 0.85rem; color: var(--text-secondary); margin-left: 8px;">
                                    📅 {{ $missao->day }}
                                </span>
                            @endif
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if(in_array($usuarioSelecionado->role->value, ['P', 'M']))
                                <button 
                                    wire:click="excluirMissao('{{ $missao->id }}')" 
                                    class="btn-primary" 
                                    style="padding: 10px; border-radius: 12px; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); display: flex; align-items: center; justify-content: center;"
                                    title="Excluir Missão"
                                    onclick="confirm('Tem certeza que deseja excluir esta missão?') || event.stopImmediatePropagation()"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#f87171" style="width: 18px; height: 18px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            @endif

                            <button 
                                wire:click="marcarComoConcluido('{{ $missao->id }}')" 
                                class="btn-primary" 
                                style="padding: 10px 16px; border-radius: 12px; background: var(--success); font-size: 0.9rem;"
                            >
                                Feito!
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif

    <!-- Modal de Playlists de Missões Sugeridas (Design Nativo da Aplicação) -->
    @if($exibirModalPlaylists)
        <div style="position: fixed; inset: 0; z-index: 1000; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); display: flex; align-items: center; justify-content: center; padding: 15px;">
            @php
                $playlistAtiva = PlaylistMissao::tryFrom($playlistSelecionada) ?? PlaylistMissao::EXPLORADORES_ROTINA;
                $todasPlaylists = PlaylistMissao::cases();
                $idadeFilho = $usuarioSelecionado?->obterIdade();
                $totalMissoesAtiva = count($playlistAtiva->missoes());
                $todasMarcadas = count($missoesSugeridasSelecionadas) === $totalMissoesAtiva;
                $basesTerapeuticas = PlaylistMissao::basesTerapeuticas();
            @endphp

            <div class="glass-card" style="margin: 0; border: 2px solid #a855f7; width: 100%; max-width: 480px; max-height: 88vh; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.6);" role="dialog" aria-modal="true" aria-labelledby="tituloModalPlaylists">
                <!-- Cabeçalho do Modal -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.4rem;" aria-hidden="true">🎧</span>
                            <h3 id="tituloModalPlaylists" style="font-size: 1.2rem; font-weight: 800; color: #c084fc; margin: 0;">Playlists de Missões</h3>
                        </div>
                        <p style="font-size: 0.82rem; color: #cbd5e1; margin-top: 4px;">
                            @if($usuarioSelecionado && in_array($usuarioSelecionado->role->value, ['S', 'D']))
                                Sugestões para <strong>{{ $usuarioSelecionado->name }}</strong>
                                @if($idadeFilho !== null) ({{ $idadeFilho }} anos) @endif
                            @else
                                Toque em uma playlist para ver e selecionar as tarefas:
                            @endif
                        </p>
                    </div>
                    <button type="button" 
                            wire:click="$set('exibirModalPlaylists', false)" 
                            style="background: none; border: none; color: #cbd5e1; cursor: pointer; font-size: 1.4rem; line-height: 1; padding: 0 4px;"
                            title="Fechar modal de playlists"
                            aria-label="Fechar modal de playlists">&times;</button>
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
                            <div wire:click="alternarExpansaoPlaylist('{{ $pl->value }}')"
                                 style="padding: 12px 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 10px; user-select: none;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 1.3rem;" aria-hidden="true">{{ $pl->icone() }}</span>
                                    <div>
                                        <div style="font-size: 0.92rem; font-weight: 700; color: {{ $estaExpandida ? '#c084fc' : '#f8fafc' }};">
                                            {{ $pl->titulo() }}
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 2px;">
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
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="color: #cbd5e1; font-size: 0.75rem; transition: transform 0.2s; display: inline-block; transform: {{ $estaExpandida ? 'rotate(180deg)' : 'rotate(0deg)' }};" aria-hidden="true">
                                        ▼
                                    </span>
                                </div>
                            </div>

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
                                            @endphp
                                            <div wire:click="alternarSelecaoMissao('{{ $chave }}')"
                                                 style="background: {{ $marcada ? 'rgba(168, 85, 247, 0.18)' : 'rgba(0, 0, 0, 0.2)' }}; border: 1px solid {{ $marcada ? '#a855f7' : 'var(--card-border)' }}; border-radius: 10px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; gap: 10px; cursor: pointer; transition: all 0.15s;">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <input type="checkbox" 
                                                           {{ $marcada ? 'checked' : '' }} 
                                                           style="cursor: pointer; accent-color: #a855f7; width: 16px; height: 16px; pointer-events: none;" />
                                                    <span style="font-size: 0.85rem; font-weight: 500; color: {{ $marcada ? '#f8fafc' : 'var(--text-secondary)' }};">
                                                        {{ $missao['descricao'] }}
                                                    </span>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                                    @if(!empty($missao['dia']))
                                                        <span style="font-size: 0.72rem; color: var(--text-secondary); background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 6px;">
                                                            📅 {{ $missao['dia'] }}
                                                        </span>
                                                    @endif
                                                    <span style="font-size: 0.82rem; color: #818cf8; font-weight: 600; background: rgba(129, 140, 248, 0.15); padding: 2px 8px; border-radius: 8px;">
                                                        🪙 +{{ $missao['moedas'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>

                <!-- Rodapé com Ações -->
                <div style="display: flex; gap: 10px; border-top: 1px solid var(--card-border); padding-top: 10px;">
                    <button type="button" 
                            wire:click="$set('exibirModalPlaylists', false)"
                            class="btn-primary" 
                            style="flex: 1; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--card-border); color: var(--text-secondary); box-shadow: none; font-size: 0.85rem; padding: 10px;">
                        Cancelar
                    </button>
                    <button type="button" 
                            wire:click="adicionarMissoesSugeridas" 
                            class="btn-primary" 
                            style="flex: 2; background: #a855f7; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.35); font-size: 0.85rem; padding: 10px; opacity: {{ count($missoesSugeridasSelecionadas) === 0 ? '0.5' : '1' }};"
                            {{ count($missoesSugeridasSelecionadas) === 0 ? 'disabled' : '' }}>
                        ➕ Adicionar à Rotina ({{ count($missoesSugeridasSelecionadas) }})
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
