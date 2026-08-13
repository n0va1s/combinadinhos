@extends('layouts.app')

@section('content')
<div class="glass-card" style="margin-top: 30px;">
    <h2 style="font-size: 1.5rem; margin-bottom: 8px; font-weight: 800; background: linear-gradient(to right, #38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Convite de Família 🏠
    </h2>
    <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 20px;">
        @php
            $roleLabel = match($role) {
                'P' => 'Pai',
                'M' => 'Mãe',
                'S' => 'Filho',
                'D' => 'Filha',
                default => $role
            };
        @endphp
        Você foi convidado para entrar na família <strong>{{ $invitation->family->name }}</strong> com o papel de <strong>{{ $roleLabel }}</strong>.
    </p>

    <form action="/invite/{{ $invitation->code }}/accept" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
        @csrf
        <input type="hidden" name="role" value="{{ $role }}">
        
        <div>
            <label for="name" style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 6px; font-weight: 600;">Seu Nome:</label>
            <input type="text" name="name" id="name" placeholder="Ex: Luísa" style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 8px; padding: 12px; color: #fff; font-family: inherit; font-size: 1rem;" required>
        </div>

        <button type="submit" class="btn-success" style="margin-top: 10px;">
            Entrar na Família 🤝
        </button>
    </form>
</div>
@endsection
