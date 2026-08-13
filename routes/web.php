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

require __DIR__.'/auth.php';
