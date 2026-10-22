<?php

use App\Models\Transaction;
use App\Enums\TransactionStatus;
use App\Actions\ResolverFilhoSelecionado;
use function Livewire\Volt\{state, mount, computed, layout};

layout('layouts.app');

state([
    'usuarioSelecionado' => null,
    'precisaSelecionar' => false,
]);

mount(function (ResolverFilhoSelecionado $resolver) {
    if (!auth()->check()) {
        return;
    }

    // Fonte única da seleção é o dashboard, que envia o filho via ?filho= na URL.
    $resultado = $resolver->executar(request()->query('filho'));
    $this->usuarioSelecionado = $resultado['filho'];
    $this->precisaSelecionar = $resultado['precisaSelecionar'];
});

// Lista cronológica de transações aprovadas do usuário selecionado.
$transacoes = computed(function () {
    if (!$this->usuarioSelecionado) {
        return collect();
    }

    return Transaction::where('user_id', $this->usuarioSelecionado->id)
        ->where('status', TransactionStatus::APROVADO)
        ->orderBy('updated_at', 'desc')
        ->get();
});

?>

<div class="w-full">
    <!-- Header -->
    <div class="header">
        <a href="{{ route('dashboard', $usuarioSelecionado ? ['filho' => $usuarioSelecionado->id] : []) }}" wire:navigate style="text-decoration: none; display: flex; align-items: center; gap: 8px; color: var(--text-primary);" aria-label="Voltar ao painel">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            <span class="header-logo">Extrato 🧾</span>
        </a>
        @if($usuarioSelecionado)
            <div class="glass-card" style="margin: 0; padding: 6px 14px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 0.9rem; font-weight: 600;" aria-label="Saldo atual de {{ $usuarioSelecionado->name }}">🪙 {{ $usuarioSelecionado->balance }}</span>
            </div>
        @endif
    </div>

    <!-- Resumo do saldo -->
    @if($usuarioSelecionado)
        <div class="glass-card" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">{{ $usuarioSelecionado->name }}</h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 2px;">Saldo atual</p>
            </div>
            <span style="font-size: 1.4rem; font-weight: 800; color: #fbbf24;" aria-label="{{ $usuarioSelecionado->balance }} moedas">
                🪙 {{ $usuarioSelecionado->balance }}
            </span>
        </div>
    @endif

    <!-- Lista de transações -->
    @if($usuarioSelecionado)
        <div style="padding: 0 15px;">
            <h2 style="font-size: 1.3rem; margin: 15px 0 10px; font-weight: 800;">Histórico</h2>

            @php $listaTransacoes = $this->transacoes; @endphp

            @if($listaTransacoes->isEmpty())
                <div class="glass-card" style="text-align: center; color: var(--text-secondary); margin: 0 0 15px 0; padding: 24px 16px;">
                    <span style="font-size: 2.2rem; display: block; margin-bottom: 8px;" aria-hidden="true">🧾</span>
                    <h4 style="font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px;">Nenhuma movimentação ainda</h4>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0;">
                        As missões concluídas e recompensas resgatadas aparecerão aqui.
                    </p>
                </div>
            @else
                @foreach($listaTransacoes as $transacao)
                    @php $ehPositivo = $transacao->amount >= 0; @endphp
                    <div class="glass-card" style="margin: 0 0 12px 0; display: flex; justify-content: space-between; align-items: center; gap: 12px; border-left: 4px solid {{ $ehPositivo ? '#10b981' : '#ef4444' }};">
                        <div style="min-width: 0;">
                            <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 4px;">{{ $transacao->detail }}</h4>
                            <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0;">
                                📅 {{ $transacao->updated_at->format('d/m/Y \à\s H:i') }}
                            </p>
                        </div>
                        <span style="flex-shrink: 0; font-size: 0.9rem; font-weight: 700; color: {{ $ehPositivo ? '#10b981' : '#f87171' }}; background: {{ $ehPositivo ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; padding: 4px 10px; border-radius: 10px; white-space: nowrap;">
                            🪙 {{ $ehPositivo ? '+' : '' }}{{ $transacao->amount }}
                        </span>
                    </div>
                @endforeach
            @endif
        </div>
    @elseif($precisaSelecionar)
        <div style="padding: 0 15px;">
            <div class="glass-card" role="status" style="text-align: center; color: var(--text-secondary); margin: 15px 0; padding: 24px 16px;">
                <span style="font-size: 2.2rem; display: block; margin-bottom: 8px;" aria-hidden="true">👆</span>
                <p style="font-size: 0.95rem; color: #f8fafc; margin: 0 0 16px;">
                    Selecione um filho no painel
                </p>
                <a href="{{ route('dashboard') }}" wire:navigate class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; font-size: 0.9rem;" aria-label="Voltar ao painel">
                    Voltar ao painel
                </a>
            </div>
        </div>
    @endif
</div>
