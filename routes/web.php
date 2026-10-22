<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

Volt::route('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

Volt::route('/extrato', 'extrato')
    ->middleware('auth')
    ->name('extrato');

Volt::route('/loja', 'loja')
    ->middleware('auth')
    ->name('loja');

Volt::route('/missoes', 'missoes')
    ->middleware('auth')
    ->name('missoes');

require __DIR__.'/auth.php';


