<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('home');
})->middleware('auth')->name('dashboard');

Route::get('/invite/{code}', [\App\Http\Controllers\InviteController::class, 'show']);
Route::post('/invite/{code}/accept', [\App\Http\Controllers\InviteController::class, 'accept']);

Route::get('/run-fresh-migration', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
        '--force' => true,
        '--seed' => true
    ]);
    return 'Banco de dados recriado e populado com sucesso!';
});

require __DIR__.'/auth.php';

