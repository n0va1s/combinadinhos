<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

Volt::route('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

require __DIR__.'/auth.php';


