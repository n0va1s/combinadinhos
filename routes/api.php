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

// Rota de configuração (pois o Render Shell é pago)
Route::get('/setup-bot', function (Request $request) {
    if ($request->query('secret') !== env('CRON_SECRET', 'secret123')) {
        abort(403, 'Unauthorized');
    }
    
    try {
        $bot = \DefStudio\Telegraph\Models\TelegraphBot::firstOrCreate(
            ['token' => env('TELEGRAM_BOT_TOKEN')],
            ['name' => 'Combinadinhos']
        );
        
        $appUrl = 'https://combinadinhos.onrender.com';
        $webhookUrl = $appUrl . '/telegraph/' . $bot->token . '/webhook';
        
        // Registra o webhook diretamente via API do Telegram (sem depender do APP_URL)
        $response = \Illuminate\Support\Facades\Http::post(
            "https://api.telegram.org/bot{$bot->token}/setWebhook",
            ['url' => $webhookUrl]
        );
        
        return response()->json([
            'status' => 'success',
            'bot_id' => $bot->id,
            'webhook_url' => $webhookUrl,
            'telegram_response' => $response->json(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});
