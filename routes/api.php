<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rota pública de diagnóstico
Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'cron_secret_set' => !empty(env('CRON_SECRET')),
        'db' => env('DB_CONNECTION'),
    ]);
});

