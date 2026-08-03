<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rota secreta para o Cron-job.org disparar as mensagens diárias
Route::get('/trigger-daily-tasks', function (Request $request) {
    if ($request->query('secret') !== env('CRON_SECRET', 'secret123')) {
        abort(403, 'Unauthorized');
    }
    
    \Illuminate\Support\Facades\Artisan::call('app:send-daily-tasks');
    return response()->json(['status' => 'success', 'output' => \Illuminate\Support\Facades\Artisan::output()]);
});
