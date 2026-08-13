<?php

use App\Models\User;
use App\Models\Family;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;
use function Livewire\Volt\mount;

layout('layouts.guest');

state([
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    'role' => 'S',
    'family_name' => '', // Se for criar uma nova família
    'family_id' => '',   // Se for vincular a uma existente
    'families' => []
]);

mount(function () {
    $this->families = Family::all();
});

rules([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
    'role' => ['required', 'string', 'in:P,M,S,D'],
    'family_id' => ['nullable', 'string'],
    'family_name' => ['nullable', 'string', 'max:255']
]);

$register = function () {
    $validated = $this->validate();

    $targetFamilyId = null;

    // Se informou um nome para criar uma nova família
    if (!empty($this->family_name)) {
        $newFamily = Family::create(['name' => $this->family_name]);
        $targetFamilyId = $newFamily->id;
    } elseif (!empty($this->family_id)) {
        $targetFamilyId = $this->family_id;
    }

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => $validated['role'],
        'family_id' => $targetFamilyId,
        'balance' => 0
    ]);

    event(new Registered($user));

    Auth::login($user);

    $this->redirect('/dashboard', navigate: true);
};

?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Role Selector -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Papel')" />
            <select wire:model="role" id="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="S">Filho</option>
                <option value="D">Filha</option>
                <option value="P">Pai</option>
                <option value="M">Mãe</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Vincular a uma Família Existente -->
        <div class="mt-4">
            <x-input-label for="family_id" :value="__('Escolha uma Família Existente')" />
            <select wire:model="family_id" id="family_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Nenhuma / Criar Nova --</option>
                @foreach($families as $f)
                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('family_id')" class="mt-2" />
        </div>

        <!-- Criar Nova Família -->
        <div class="mt-4">
            <x-input-label for="family_name" :value="__('Ou digite o nome para Criar Nova Família')" />
            <x-text-input wire:model="family_name" id="family_name" class="block mt-1 w-full" type="text" placeholder="Ex: Família Silva" />
            <x-input-error :messages="$errors->get('family_name')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
