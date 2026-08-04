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
        $identificadorChatGrupo = config('combinadinhos.group_chat_id');
        if (!$identificadorChatGrupo) {
            $this->error('GROUP_CHAT_ID não configurado.');
            return;
        }

        $botTelegram = TelegraphBot::first();
        if (!$botTelegram) {
            $this->error('Bot não cadastrado.');
            return;
        }

        $chatGrupo = TelegraphChat::where('chat_id', $identificadorChatGrupo)->first();
        if (!$chatGrupo) {
            // Cria o chat se não existir localmente
            $chatGrupo = $botTelegram->chats()->create([
                'chat_id' => $identificadorChatGrupo,
                'name' => 'Grupo Familia',
            ]);
        }

        $diasDaSemana = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terça',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sábado',
        ];
        $diaSemanaAtual = $diasDaSemana[now()->dayOfWeek];

        $listaMissoes = Mission::whereNull('day')
            ->orWhere('day', '')
            ->orWhereRaw('LOWER(day) = ?', [$diaSemanaAtual])
            ->get();

        if ($listaMissoes->isEmpty()) {
            $this->info('Nenhuma missão para hoje.');
            return;
        }

        $textoMensagem = "🎯 *MISSÕES DE HOJE* 🎯\n\n";
        foreach ($listaMissoes as $umaMissao) {
            $textoMensagem .= "• {$umaMissao->description} (+{$umaMissao->coins} pts)\n";
        }
        
        $textoMensagem .= "\nBora lá cumprir tudo!";
        
        $chatGrupo->html($textoMensagem)->send();
        $this->info('Missões enviadas.');
    }
}
