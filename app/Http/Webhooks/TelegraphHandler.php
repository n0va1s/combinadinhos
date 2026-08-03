<?php

namespace App\Http\Webhooks;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Stringable;
use App\Models\User;
use App\Models\Mission;
use App\Models\Reward;
use App\Models\Transaction;

class TelegraphHandler extends WebhookHandler
{
    public function start(): void
    {
        $this->chat->html("Olá! Sou o bot dos Combinadinhos.\n\nPara iniciar o dia, use o comando /missoes.\nSe você é pai ou mãe, lembre-se de configurar seu ID (".$this->message->from()->id().") no banco de dados com a role 'pai'.")->send();
    }

    public function saldo(): void
    {
        $telegramId = $this->message->from()->id();
        $user = User::where('telegram_id', $telegramId)->first();
        $balance = $user ? $user->balance : 0;
        
        $this->chat->html("Seu saldo atual é de {$balance} Combinadinhos!")->send();
    }
    
    public function missoes(): void
    {
        $missions = Mission::all();
        if ($missions->isEmpty()) {
            $this->chat->html("Não há missões cadastradas!")->send();
            return;
        }

        $text = "🎯 *MISSÕES DE HOJE* 🎯\n\n";
        
        // Em um app completo, enviaríamos botões inline para concluir missões
        foreach ($missions as $mission) {
            $text .= "• {$mission->description} (+{$mission->coins} pts)\n";
        }
        
        $text .= "\nBora lá cumprir tudo!";
        $this->chat->html($text)->send();
    }
    
    public function lojinha(): void
    {
        $rewards = Reward::all();
        if ($rewards->isEmpty()) {
            $this->chat->html("A lojinha está vazia!")->send();
            return;
        }
        
        $text = "🎁 *LOJINHA DOS COMBINADINHOS* 🎁\n\n";
        foreach ($rewards as $reward) {
            $text .= "• {$reward->description} (Custa: {$reward->cost} pts)\n";
        }
        
        $this->chat->html($text)->send();
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        if ($text->startsWith('/missoes-add')) {
            $this->addMission($text);
            return;
        }
        
        if ($text->startsWith('/lojinha-add')) {
            $this->addReward($text);
            return;
        }
        
        $this->chat->html("Comando desconhecido. Use /start para ver as opções.")->send();
    }
    
    private function isParent(): bool
    {
        $telegramId = $this->message->from()->id();
        $user = User::where('telegram_id', $telegramId)->first();
        return $user && in_array(strtolower($user->role), ['pai', 'mãe', 'mae']);
    }

    private function addMission(Stringable $text): void
    {
        if (!$this->isParent()) {
            $this->chat->html("Apenas pais podem adicionar missões!")->send();
            return;
        }
        
        $content = trim(str_replace('/missoes-add', '', $text));
        $parts = array_map('trim', explode(',', $content));
        
        if (count($parts) < 2) {
            $this->chat->html("Uso: /missoes-add Descrição, Valor, [Dia]")->send();
            return;
        }
        
        $description = $parts[0];
        $coins = (int) $parts[1];
        $day = $parts[2] ?? null;
        
        Mission::create([
            'description' => $description,
            'coins' => $coins,
            'day' => $day,
        ]);
        
        $this->chat->html("✅ Missão adicionada com sucesso!\n🎯 {$description} ({$coins} pts)")->send();
    }

    private function addReward(Stringable $text): void
    {
        if (!$this->isParent()) {
            $this->chat->html("Apenas pais podem adicionar recompensas!")->send();
            return;
        }
        
        $content = trim(str_replace('/lojinha-add', '', $text));
        $parts = array_map('trim', explode(',', $content));
        
        if (count($parts) < 2) {
            $this->chat->html("Uso: /lojinha-add Descrição, Custo")->send();
            return;
        }
        
        $description = $parts[0];
        $cost = (int) $parts[1];
        
        Reward::create([
            'description' => $description,
            'cost' => $cost,
        ]);
        
        $this->chat->html("✅ Recompensa adicionada com sucesso!\n🎁 {$description} (Custa: {$cost} pts)")->send();
    }
}
