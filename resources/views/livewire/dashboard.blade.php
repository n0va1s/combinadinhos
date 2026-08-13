<?php

use App\Models\User;
use App\Models\Mission;
use App\Models\Transaction;
use App\Models\FamilyInvitation;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

state([
    'users' => [],
    'selectedUserId' => null,
    'selectedUser' => null,
    'missions' => [],
    'soundChecked' => false,
    'lastMissionsHash' => '',
    'invitationLink' => null,
    'selectedInviteRole' => 'S',
    'pendingTransactions' => [],
]);

mount(function () {
    if (auth()->check()) {
        $this->selectedUser = auth()->user();
        $this->selectedUserId = $this->selectedUser->id;
    }
    $this->loadUsers();
    $this->loadMissions();
    $this->loadPendingTransactions();
});

$loadUsers = function () {
    // Se o usuário selecionado estiver vinculado a uma família, carregamos apenas os membros daquela família.
    if ($this->selectedUser && $this->selectedUser->family_id) {
        $this->users = User::where('family_id', $this->selectedUser->family_id)->get();
    } else {
        $this->users = User::all();
    }

    if ($this->selectedUserId) {
        $this->selectedUser = User::find($this->selectedUserId);
    }
};

$loadPendingTransactions = function () {
    if (auth()->check() && in_array(auth()->user()->role->value, ['P', 'M'])) {
        $familyUserIds = User::where('family_id', auth()->user()->family_id)->pluck('id');
        $this->pendingTransactions = Transaction::whereIn('user_id', $familyUserIds)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    } else {
        $this->pendingTransactions = [];
    }
};

$loadMissions = function () {
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

    $query = Mission::query();

    if ($this->selectedUser && $this->selectedUser->family_id) {
        $query->where('family_id', $this->selectedUser->family_id);
    }

    $this->missions = $query->where(function ($q) use ($diaAtualPt) {
        $q->where('day', $diaAtualPt)
          ->orWhereNull('day')
          ->orWhere('day', '')
          ->orWhere('day', 'Hoje');
    })->orderBy('description', 'asc')->get();
    
    $newHash = md5(json_encode($this->missions->pluck('id', 'updated_at')->toArray()));
    
    if ($this->lastMissionsHash && $this->lastMissionsHash !== $newHash) {
        $descList = $this->missions->pluck('description')->implode(', ');
        \Flux::toast(
            heading: 'Tarefas Atualizadas! 📝',
            text: 'Novas tarefas disponíveis: ' . $descList,
            variant: 'info'
        );
    }
    $this->lastMissionsHash = $newHash;
};

$selectUser = function ($id) {
    $this->selectedUserId = $id;
    $this->selectedUser = User::find($id);
    $this->loadUsers();
    $this->loadMissions();
};

$addQuickTask = function ($description, $coins) {
    if (!$this->selectedUser || !$this->selectedUser->family_id) return;

    Mission::create([
        'description' => $description,
        'coins' => (int) $coins,
        'day' => 'Hoje',
        'family_id' => $this->selectedUser->family_id
    ]);
    $this->loadMissions();
};

$generateInvite = function () {
    if (!$this->selectedUser || !$this->selectedUser->family_id) return;

    $code = (string) \Illuminate\Support\Str::uuid();

    FamilyInvitation::create([
        'family_id' => $this->selectedUser->family_id,
        'role' => $this->selectedInviteRole,
        'code' => $code,
    ]);

    $this->invitationLink = url("/invite/{$code}?role=" . $this->selectedInviteRole);
};

$markAsDone = function ($missionId) {
    if (!$this->selectedUser) {
        return;
    }

    $mission = Mission::find($missionId);
    if (!$mission) return;

    $loggedInUser = auth()->user();
    $isParent = $loggedInUser && in_array($loggedInUser->role->value, ['P', 'M']);
    $isNegative = $mission->coins < 0;

    if ($isParent) {
        // Pais aprovam direto.
        $this->selectedUser->balance += $mission->coins;
        $this->selectedUser->save();

        Transaction::create([
            'action' => $isNegative ? 'Perdeu' : 'Ganhou',
            'user_name' => $this->selectedUser->name,
            'detail' => 'Realizou: ' . $mission->description,
            'amount' => $mission->coins,
            'status' => 'approved',
            'user_id' => $this->selectedUser->id,
        ]);

        $this->loadUsers();

        $title = $isNegative ? 'Continue se esforçando! 💪' : 'Parabéns! 🎉';
        $message = $isNegative 
            ? "{$this->selectedUser->name} perdeu " . abs($mission->coins) . " moedas: {$mission->description}" 
            : "{$this->selectedUser->name} ganhou {$mission->coins} moedas!";

        \Flux::toast(
            heading: $title,
            text: $message,
            variant: $isNegative ? 'danger' : 'success'
        );

        $this->dispatch('play-task-alert', [
            'sound' => $isNegative ? 'suspense' : 'applause'
        ]);
    } else {
        // Filhos enviando para aprovação (mesmo as negativas)
        Transaction::create([
            'action' => $isNegative ? 'Perdeu' : 'Ganhou',
            'user_name' => $this->selectedUser->name,
            'detail' => 'Aguardando Aprovação: ' . $mission->description,
            'amount' => $mission->coins,
            'status' => 'pending',
            'user_id' => $this->selectedUser->id,
        ]);

        \Flux::toast(
            heading: 'Enviado para Aprovação! ⏳',
            text: "A tarefa '{$mission->description}' foi enviada para aprovação.",
            variant: 'info'
        );
    }
};

$approveTransaction = function ($transactionId) {
    $transaction = Transaction::find($transactionId);
    if ($transaction && $transaction->status === 'pending') {
        $transaction->status = 'approved';
        $transaction->detail = str_replace('Aguardando Aprovação: ', 'Realizou: ', $transaction->detail);
        $transaction->save();
        
        $user = User::find($transaction->user_id);
        if ($user) {
            $user->balance += $transaction->amount;
            $user->save();
        }
        $this->loadPendingTransactions();
        $this->loadUsers();
        
        $isNegative = $transaction->amount < 0;

        \Flux::toast(
            heading: $isNegative ? 'Punição Aplicada ⚖️' : 'Aprovado! ✅',
            text: "Você aprovou a tarefa para {$user->name}.",
            variant: $isNegative ? 'danger' : 'success'
        );
        
        $this->dispatch('play-task-alert', [
            'sound' => $isNegative ? 'suspense' : 'applause'
        ]);
    }
};

$rejectTransaction = function ($transactionId) {
    $transaction = Transaction::find($transactionId);
    if ($transaction && $transaction->status === 'pending') {
        $transaction->status = 'rejected';
        $transaction->save();
        $this->loadPendingTransactions();
        
        \Flux::toast(
            heading: 'Recusado ❌',
            text: 'A missão foi recusada.',
            variant: 'danger'
        );
    }
};

$deleteMission = function ($missionId) {
    $mission = Mission::find($missionId);
    if ($mission) {
        $mission->delete();
        $this->loadMissions();
    }
};

$logout = function () {
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
        @if($selectedUser)
            <div style="display: flex; align-items: center; gap: 8px;">
                <div class="glass-card" style="margin: 0; padding: 6px 14px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 0.9rem; font-weight: 600;">🪙 {{ $selectedUser->balance }}</span>
                </div>
                <button wire:click="logout" class="btn-primary" style="background: #ef4444; font-size: 0.85rem; padding: 6px 12px; box-shadow: none;">Sair</button>
            </div>
        @endif
    </div>

    <!-- Bloco de Perfil / Login -->
    <div class="glass-card" style="margin-top: 5px;">
        @if($selectedUser)
            <h3 style="font-size: 1.2rem; margin-bottom: 4px; font-weight: 700;">Olá, {{ $selectedUser->name }}!</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 8px;">
                Seu perfil é de <strong>{{ $selectedUser->role->label() }}</strong>
            </p>
            <p style="font-size: 0.85rem; color: #818cf8; font-weight: 600;">
                Marque as missões realizadas hoje (positivas ou negativas)
            </p>
            
            @if(in_array($selectedUser->role->value, ['P', 'M']))
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--card-border); display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 0.85rem; color: var(--text-secondary);">Convidar:</span>
                        <select wire:model="selectedInviteRole" style="background: rgba(0, 0, 0, 0.3); border: 1px solid var(--card-border); border-radius: 8px; padding: 6px 12px; color: #fff; font-size: 0.85rem; font-family: inherit; outline: none; cursor: pointer;">
                            <option value="S" style="color: #000;">Filho</option>
                            <option value="D" style="color: #000;">Filha</option>
                            <option value="P" style="color: #000;">Pai</option>
                            <option value="M" style="color: #000;">Mãe</option>
                        </select>
                    </div>
                    <button wire:click="generateInvite" class="btn-primary" style="background: var(--accent); padding: 8px 16px; font-size: 0.85rem; border-radius: 8px; display: flex; align-items: center; gap: 6px; box-shadow: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                        </svg>
                        Gerar Convite
                    </button>
                </div>
            @endif

            @if($invitationLink)
                <div style="margin-top: 12px; background: rgba(99, 102, 241, 0.15); padding: 12px; border-radius: 12px; font-size: 0.85rem; border: 1px dashed var(--accent);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        @php
                            $selectedRoleLabel = match($selectedInviteRole) {
                                'P' => 'Pai',
                                'M' => 'Mãe',
                                'S' => 'Filho',
                                'D' => 'Filha',
                                default => $selectedInviteRole
                            };
                        @endphp
                        <strong>Convide o {{ $selectedRoleLabel }}:</strong>
                        <button onclick="document.getElementById('headerInviteLink').select(); document.execCommand('copy'); alert('Link copiado!');" style="background: var(--accent); border: none; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">Copiar</button>
                    </div>
                    <input type="text" id="headerInviteLink" readonly value="{{ $invitationLink }}" style="width: 100%; background: transparent; border: none; color: #818cf8; font-family: monospace; outline: none;">
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

    <div wire:poll.10s="loadMissions"></div>

    <!-- Aguardando Aprovação (Pais) -->
    @if($selectedUser && in_array($selectedUser->role->value, ['P', 'M']) && count($pendingTransactions) > 0)
        <div style="padding: 0 15px;">
            <h2 style="font-size: 1.3rem; margin: 15px 0 10px; font-weight: 800; color: #fbbf24;">Aguardando Aprovação ⏳</h2>
            @foreach($pendingTransactions as $transaction)
                <div class="glass-card" style="margin: 0 0 15px 0; display: flex; flex-direction: column; gap: 10px; border-left: 4px solid #fbbf24;">
                    <div>
                        <h4 style="font-size: 1rem; font-weight: 700; color: #f8fafc;">{{ $transaction->user_name }}</h4>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">{{ $transaction->detail }}</p>
                        <span style="display: inline-block; margin-top: 6px; font-size: 0.85rem; color: #fbbf24; font-weight: 600; background: rgba(251, 191, 36, 0.15); padding: 2px 8px; border-radius: 8px;">
                            🪙 {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }} moedas
                        </span>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 5px;">
                        <button wire:click="approveTransaction('{{ $transaction->id }}')" class="btn-primary" style="flex: 1; padding: 8px; background: var(--success); font-size: 0.9rem;">
                            Aprovar
                        </button>
                        <button wire:click="rejectTransaction('{{ $transaction->id }}')" class="btn-primary" style="flex: 1; padding: 8px; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; font-size: 0.9rem; box-shadow: none;">
                            Recusar
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Missões do Dia -->
    @if($selectedUser)
        <div style="padding: 0 15px;">
            <h2 style="font-size: 1.3rem; margin: 15px 0 10px; font-weight: 800;">Missões do Dia</h2>
            
            @if(count($missions) === 0)
                <div class="glass-card" style="text-align: center; color: var(--text-secondary); margin: 0 0 15px 0;">
                    Nenhuma missão cadastrada para esta família ainda.
                </div>
            @else
                @foreach($missions as $mission)
                    <div class="glass-card" style="margin: 0 0 15px 0; display: flex; justify-content: space-between; align-items: center; gap: 15px; border-left: 4px solid {{ $mission->coins < 0 ? '#ef4444' : 'transparent' }};">
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 4px;">{{ $mission->description }}</h4>
                            @if($mission->coins < 0)
                                <span style="font-size: 0.85rem; color: #f87171; font-weight: 600; background: rgba(239, 68, 68, 0.15); padding: 2px 8px; border-radius: 8px;">
                                    🪙 {{ $mission->coins }} moedas
                                </span>
                            @else
                                <span style="font-size: 0.85rem; color: #818cf8; font-weight: 600; background: rgba(129, 140, 248, 0.15); padding: 2px 8px; border-radius: 8px;">
                                    🪙 +{{ $mission->coins }} moedas
                                </span>
                            @endif
                            @if($mission->day)
                                <span style="font-size: 0.85rem; color: var(--text-secondary); margin-left: 8px;">
                                    📅 {{ $mission->day }}
                                </span>
                            @endif
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if(in_array($selectedUser->role->value, ['P', 'M']))
                                <button 
                                    wire:click="deleteMission('{{ $mission->id }}')" 
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
                                wire:click="markAsDone('{{ $mission->id }}')" 
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

    <!-- Painel dos Pais (Notificar e Convidar) -->
    @if($selectedUser && in_array($selectedUser->role->value, ['P', 'M']))
        <div class="glass-card">
            <h3 style="font-size: 1.1rem; margin-bottom: 12px; color: #c084fc;">Painel dos Pais (Notificar)</h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 12px;">
                Adicione uma nova tarefa rápida para notificar imediatamente as crianças com aviso em texto e sinal sonoro!
            </p>
            <form wire:submit.prevent style="display: flex; flex-direction: column; gap: 10px;">
                <input type="text" id="newTaskDesc" placeholder="Ex: Escovar os dentes" style="background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 10px; color: #fff; font-family: inherit;" required>
                <input type="number" id="newTaskCoins" placeholder="Quantas moedas vale? Ex: 10" style="background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 10px; color: #fff; font-family: inherit;" required>
                <button type="submit" class="btn-primary" style="background: #a855f7;">Adicionar e Chamar Atenção 🔔</button>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.addEventListener('submit', (e) => {
                    const descInput = document.getElementById('newTaskDesc');
                    const coinsInput = document.getElementById('newTaskCoins');
                    if (descInput && coinsInput && e.target.contains(descInput)) {
                        @this.call('addQuickTask', descInput.value, coinsInput.value);
                        descInput.value = '';
                        coinsInput.value = '';
                    }
                });
            });
        </script>
    @endif
</div>
