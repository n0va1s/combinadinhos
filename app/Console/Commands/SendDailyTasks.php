<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use App\Models\Mission;
use Carbon\Carbon;

class SendDailyTasks extends Command
{
    protected $signature = 'app:send-daily-tasks';
    protected $description = 'Envia as tarefas diárias para o grupo do Telegram';

    public function handle()
    {
        $groupChatId = config('combinadinhos.group_chat_id');
        if (!$groupChatId) {
            $this->error('GROUP_CHAT_ID não configurado.');
            return;
        }

        $bot = TelegraphBot::first();
        if (!$bot) {
            $this->error('Bot não cadastrado.');
            return;
        }

        $chat = TelegraphChat::where('chat_id', $groupChatId)->first();
        if (!$chat) {
            // Cria o chat se não existir localmente
            $chat = $bot->chats()->create([
                'chat_id' => $groupChatId,
                'name' => 'Grupo Familia',
            ]);
        }

        $missions = Mission::all();
        if ($missions->isEmpty()) {
            $this->info('Nenhuma missão para hoje.');
            return;
        }

        $text = "🎯 *MISSÕES DE HOJE* 🎯\n\n";
        foreach ($missions as $mission) {
            $text .= "• {$mission->description} (+{$mission->coins} pts)\n";
        }
        
        $text .= "\nBora lá cumprir tudo!";
        
        $chat->html($text)->send();
        $this->info('Missões enviadas.');
    }
}
