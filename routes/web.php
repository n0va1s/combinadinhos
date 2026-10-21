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

require __DIR__.'/auth.php';


