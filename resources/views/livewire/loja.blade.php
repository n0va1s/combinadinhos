<?php

use App\Models\User;
use App\Models\Reward;
use App\Models\Transaction;
use App\Enums\TransactionStatus;
use App\Actions\ResolverFilhoSelecionado;
use function Livewire\Volt\{state, mount, layout};

layout('layouts.app');

state([
    'filho' => null,
    'ehResponsavel' => false,
    'precisaSelecionar' => false,
    'recompensas' => [],
    'descricaoNovaRecompensa' => '',
    'custoNovaRecompensa' => '',
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
        $this->carregarRecompensas();
    }
});

$carregarRecompensas = function () {
    if ($this->filho) {
        $this->recompensas = Reward::where('user_id', $this->filho->id)->orderBy('cost', 'asc')->get();
    } else {
        $this->recompensas = [];
    }
};

$adicionarRecompensa = function () {
    if (!$this->filho) return;
    if (!$this->descricaoNovaRecompensa || !$this->custoNovaRecompensa) return;

    Reward::create([
        'description' => $this->descricaoNovaRecompensa,
        'cost' => (int) $this->custoNovaRecompensa,
        'user_id' => $this->filho->id,
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
    if (!$this->filho) return;

    $recompensa = Reward::find($idRecompensa);
    if (!$recompensa) return;

    $usuarioLogado = auth()->user();
    $ehResponsavel = $usuarioLogado && in_array($usuarioLogado->role->value, ['P', 'M']);

    if ($ehResponsavel) {
        $this->filho->balance -= $recompensa->cost;
        $this->filho->save();

        Transaction::create([
            'action' => 'Resgatou',
            'user_name' => $this->filho->name,
            'detail' => 'Resgatou recompensa: ' . $recompensa->description,
            'amount' => -$recompensa->cost,
            'status' => TransactionStatus::APROVADO,
            'user_id' => $this->filho->id,
        ]);

        // Sem carregarUsuarios aqui; recarregamos o filho do banco para refletir o novo saldo.
        $this->filho = User::find($this->filho->id);

        \Flux::toast(
            heading: 'Recompensa Resgatada! 🎉',
            text: "{$this->filho->name} resgatou: {$recompensa->description}",
            variant: 'success'
        );

        $this->dispatch('play-task-alert', ['sound' => 'applause']);
    } else {
        if ($this->filho->balance < $recompensa->cost) {
            \Flux::toast(
                heading: 'Saldo Insuficiente 😢',
                text: "Você precisa de mais " . ($recompensa->cost - $this->filho->balance) . " moedas.",
                variant: 'danger'
            );
            return;
        }

        Transaction::create([
            'action' => 'Resgate',
            'user_name' => $this->filho->name,
            'detail' => 'Aguardando Aprovação (Resgate): ' . $recompensa->description,
            'amount' => -$recompensa->cost,
            'status' => TransactionStatus::PENDENTE,
            'user_id' => $this->filho->id,
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

?>

<div class="w-full">
    <!-- Header -->
    <div class="header">
        <a href="{{ route('dashboard') }}" wire:navigate style="text-decoration: none; display: flex; align-items: center; gap: 8px; color: var(--text-primary);" aria-label="Voltar ao painel">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            <span class="header-logo">Loja de Recompensas 🎁</span>
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
        <!-- Seção de Recompensas -->
        <div style="padding: 0 15px; margin-top: 15px; margin-bottom: 15px;">
            <div class="glass-card" style="margin: 0; border: 2px solid #a855f7;">
                @if(count($recompensas) === 0)
                    <p style="font-size: 0.9rem; color: var(--text-secondary); text-align: center; margin: 0;">
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
                                        <button wire:click="excluirRecompensa('{{ $recompensa->id }}')" style="background: rgba(239, 68, 68, 0.2); border: none; color: #f87171; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer;" title="Excluir recompensa" aria-label="Excluir recompensa {{ $recompensa->description }}" onclick="confirm('Excluir recompensa?') || event.stopImmediatePropagation()">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    @endif
                                    <button wire:click="resgatarRecompensa('{{ $recompensa->id }}')" class="btn-primary" style="background: var(--accent); font-size: 0.85rem; padding: 6px 12px; box-shadow: none;" aria-label="Resgatar recompensa {{ $recompensa->description }}">
                                        Resgatar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(auth()->check() && in_array(auth()->user()->role->value, ['P', 'M']))
                    <div style="border-top: 1px solid var(--card-border); padding-top: 15px; margin-top: 15px;">
                        <h4 style="font-size: 0.95rem; margin-bottom: 10px; color: #e2e8f0;">Adicionar Recompensa (Pais)</h4>
                        <form wire:submit.prevent="adicionarRecompensa" style="display: flex; gap: 8px;">
                            <label for="descricao-nova-recompensa" class="sr-only">Descrição da recompensa</label>
                            <input id="descricao-nova-recompensa" type="text" wire:model="descricaoNovaRecompensa" placeholder="Ex: Sorvete" style="flex: 2; background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 8px; color: #fff; font-size: 0.85rem;" required>
                            <label for="custo-nova-recompensa" class="sr-only">Custo em moedas</label>
                            <input id="custo-nova-recompensa" type="number" wire:model="custoNovaRecompensa" placeholder="Valor" style="flex: 1; background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 8px; color: #fff; font-size: 0.85rem;" required>
                            <button type="submit" class="btn-primary" style="background: #a855f7; padding: 8px 12px; font-size: 0.85rem; box-shadow: none;" aria-label="Adicionar recompensa">+</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
