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
        $identificadorTelegram = $this->message->from()->id();
        $this->chat->html("Olá! Sou o bot dos Combinadinhos.\n\nPara iniciar o dia, use o comando /missoes.\nSe você é pai ou mãe, lembre-se de configurar seu ID (".$identificadorTelegram.") no banco de dados com a role 'pai'.")->send();
    }

    public function saldo(): void
    {
        $identificadorTelegram = $this->message->from()->id();
        $usuarioEncontrado = User::where('telegram_id', $identificadorTelegram)->first();
        
        if (!$usuarioEncontrado) {
            $this->chat->html("Usuário não cadastrado! Use o comando /start para ver seu ID e peça para os pais realizarem seu cadastro.")->send();
            return;
        }

        $saldoAtual = $usuarioEncontrado->balance;
        $this->chat->html("Seu saldo atual é de {$saldoAtual} Combinadinhos!")->send();
    }
    
    public function missoes(): void
    {
        $listaMissoes = Mission::all();
        if ($listaMissoes->isEmpty()) {
            $this->chat->html("Não há missões cadastradas!")->send();
            return;
        }

        $textoMensagem = "🎯 *MISSÕES DE HOJE* 🎯\n\nEscolha uma missão para marcar como feita ou aplicar:";
        $tecladoBotoes = Keyboard::make();
        
        foreach ($listaMissoes as $umaMissao) {
            $rotuloBotao = $umaMissao->coins >= 0 
                ? "✅ {$umaMissao->description} (+{$umaMissao->coins} pts)"
                : "⚠️ {$umaMissao->description} ({$umaMissao->coins} pts)";

            $tecladoBotoes->row([
                Button::make($rotuloBotao)
                    ->action('feito_missao')
                    ->param('id', $umaMissao->id)
            ]);
        }
        
        $this->chat->html($textoMensagem)->keyboard($tecladoBotoes)->send();
    }
    
    public function lojinha(): void
    {
        $listaRecompensas = Reward::all();
        if ($listaRecompensas->isEmpty()) {
            $this->chat->html("A lojinha está vazia!")->send();
            return;
        }
        
        $textoMensagem = "🎁 *LOJINHA DOS COMBINADINHOS* 🎁\n\nEscolha uma recompensa para comprar:";
        $tecladoBotoes = Keyboard::make();

        foreach ($listaRecompensas as $umaRecompensa) {
            $tecladoBotoes->row([
                Button::make("🛍️ {$umaRecompensa->description} (Custa: {$umaRecompensa->cost} pts)")
                    ->action('comprar_recompensa')
                    ->param('id', $umaRecompensa->id)
            ]);
        }
        
        $this->chat->html($textoMensagem)->keyboard($tecladoBotoes)->send();
    }

    protected function handleUnknownCommand(Stringable $textoComando): void
    {
        if ($textoComando->startsWith('/missoes-add')) {
            $this->adicionarMissao($textoComando);
            return;
        }
        
        if ($textoComando->startsWith('/lojinha-add')) {
            $this->adicionarRecompensa($textoComando);
            return;
        }
        
        $this->chat->html("Comando desconhecido. Use /start para ver as opções.")->send();
    }
    
    private function verificarSeEhResponsavel(): bool
    {
        $identificadorTelegram = $this->getTelegramUserId();
        $usuarioEncontrado = User::where('telegram_id', $identificadorTelegram)->first();
        return $usuarioEncontrado && in_array(strtolower($usuarioEncontrado->role), ['pai', 'mãe', 'mae']);
    }

    private function getTelegramUserId(): int
    {
        if ($this->callbackQuery) {
            return $this->callbackQuery->from()->id();
        }
        return $this->message->from()->id();
    }

    private function adicionarMissao(Stringable $textoComando): void
    {
        if (!$this->verificarSeEhResponsavel()) {
            $this->chat->html("Apenas pais ou mães podem adicionar missões!")->send();
            return;
        }
        
        $conteudoComando = trim(str_replace('/missoes-add', '', $textoComando));
        $partesComando = array_map('trim', explode(',', $conteudoComando));
        
        if (count($partesComando) < 2) {
            $this->chat->html("Uso: /missoes-add Descrição, Valor, [Dia]")->send();
            return;
        }
        
        $descricaoMissao = $partesComando[0];
        $moedasMissao = (int) $partesComando[1];
        $diaMissao = $partesComando[2] ?? null;
        
        Mission::create([
            'description' => $descricaoMissao,
            'coins' => $moedasMissao,
            'day' => $diaMissao,
        ]);
        
        $this->chat->html("✅ Missão adicionada com sucesso!\n🎯 {$descricaoMissao} ({$moedasMissao} pts)")->send();
    }

    private function adicionarRecompensa(Stringable $textoComando): void
    {
        if (!$this->verificarSeEhResponsavel()) {
            $this->chat->html("Apenas pais ou mães podem adicionar recompensas!")->send();
            return;
        }
        
        $conteudoComando = trim(str_replace('/lojinha-add', '', $textoComando));
        $partesComando = array_map('trim', explode(',', $conteudoComando));
        
        if (count($partesComando) < 2) {
            $this->chat->html("Uso: /lojinha-add Descrição, Custo")->send();
            return;
        }
        
        $descricaoRecompensa = $partesComando[0];
        $custoRecompensa = (int) $partesComando[1];
        
        Reward::create([
            'description' => $descricaoRecompensa,
            'cost' => $custoRecompensa,
        ]);
        
        $this->chat->html("✅ Recompensa adicionada com sucesso!\n🎁 {$descricaoRecompensa} (Custa: {$custoRecompensa} pts)")->send();
    }

    /* ---- CALLBACK ACTIONS ---- */

    public function feito_missao(): void
    {
        $missaoId = $this->data->get('id');
        $umaMissao = Mission::find($missaoId);

        if (!$umaMissao) {
            $this->reply("Missão não encontrada!");
            return;
        }

        $identificadorTelegram = $this->getTelegramUserId();
        $usuarioClicou = User::where('telegram_id', $identificadorTelegram)->first();

        if (!$usuarioClicou) {
            $this->reply("Você precisa estar cadastrado no sistema para realizar esta ação!", true);
            return;
        }

        $usuarioEhResponsavel = in_array(strtolower($usuarioClicou->role), ['pai', 'mãe', 'mae']);

        // Se a missão for negativa (penalidade)
        if ($umaMissao->coins < 0) {
            if (!$usuarioEhResponsavel) {
                $this->reply("Apenas pais ou mães podem aplicar penalidades!", true);
                return;
            }

            $listaFilhos = User::where('role', 'filho')->get();
            if ($listaFilhos->isEmpty()) {
                $this->reply("Nenhum filho cadastrado para receber a penalidade!", true);
                return;
            }

            $textoMensagem = "⚠️ *APLICAR PENALIDADE* ⚠️\n\nSelecione qual filho deve receber a penalidade da missão:\n*{$umaMissao->description}* ({$umaMissao->coins} pts)";
            $tecladoBotoes = Keyboard::make();

            foreach ($listaFilhos as $umFilho) {
                $tecladoBotoes->row([
                    Button::make("👤 {$umFilho->name}")
                        ->action('aplicar_penalidade')
                        ->param('missao_id', $umaMissao->id)
                        ->param('filho_id', $umFilho->id)
                ]);
            }

            $this->chat->html($textoMensagem)->keyboard($tecladoBotoes)->send();
            $this->reply("Selecione o filho.");
            return;
        }

        // Se a missão for positiva
        if ($usuarioEhResponsavel) {
            $this->reply("Pais/mães não completam missões! Apenas filhos podem marcar como feito.", true);
            return;
        }

        // Envia solicitação de aprovação para os pais
        $textoSolicitacao = "🙋‍♂️ *SOLICITAÇÃO DE PONTOS* 🙋‍♂️\n\n👤 *{$usuarioClicou->name}* marcou a missão *{$umaMissao->description}* como feita!\n💰 Valor: *+{$umaMissao->coins} pts*\n\nPais, por favor aprovem a realização.";
        
        $tecladoAprovacao = Keyboard::make()->row([
            Button::make("👍 Aprovar")
                ->action('aprovar_missao')
                ->param('missao_id', $umaMissao->id)
                ->param('filho_id', $usuarioClicou->id)
        ]);

        $this->chat->html($textoSolicitacao)->keyboard($tecladoAprovacao)->send();
        $this->reply("Solicitação enviada para aprovação dos pais!");
    }

    public function aprovar_missao(): void
    {
        $identificadorTelegram = $this->getTelegramUserId();
        $usuarioClicou = User::where('telegram_id', $identificadorTelegram)->first();

        if (!$usuarioClicou || !in_array(strtolower($usuarioClicou->role), ['pai', 'mãe', 'mae'])) {
            $this->reply("Apenas pais ou mães podem aprovar missões!", true);
            return;
        }

        $missaoId = $this->data->get('missao_id');
        $filhoId = $this->data->get('filho_id');

        $umaMissao = Mission::find($missaoId);
        $umFilho = User::find($filhoId);

        if (!$umaMissao || !$umFilho) {
            $this->reply("Erro: Missão ou filho não encontrado.");
            return;
        }

        // Creditar moedas
        $umFilho->balance += $umaMissao->coins;
        $umFilho->save();

        // Registrar transação
        Transaction::create([
            'action' => 'missao',
            'user_name' => $umFilho->name,
            'detail' => "Completou: {$umaMissao->description}",
            'amount' => $umaMissao->coins,
        ]);

        $this->reply("Missão aprovada!");

        $textoSucesso = "✅ *Missão Aprovada!*\n\n👤 *{$umFilho->name}* realizou a missão *{$umaMissao->description}* e ganhou *+{$umaMissao->coins} pts*!\n💰 Aprovado por: *{$usuarioClicou->name}*";
        
        $this->chat->edit($this->messageId)
            ->html($textoSucesso)
            ->send();
    }

    public function aplicar_penalidade(): void
    {
        $identificadorTelegram = $this->getTelegramUserId();
        $usuarioClicou = User::where('telegram_id', $identificadorTelegram)->first();

        if (!$usuarioClicou || !in_array(strtolower($usuarioClicou->role), ['pai', 'mãe', 'mae'])) {
            $this->reply("Apenas pais ou mães podem aplicar penalidades!", true);
            return;
        }

        $missaoId = $this->data->get('missao_id');
        $filhoId = $this->data->get('filho_id');

        $umaMissao = Mission::find($missaoId);
        $umFilho = User::find($filhoId);

        if (!$umaMissao || !$umFilho) {
            $this->reply("Erro: Missão ou filho não encontrado.");
            return;
        }

        // Deduzir moedas (adicionar valor negativo)
        $umFilho->balance += $umaMissao->coins;
        $umFilho->save();

        // Registrar transação
        Transaction::create([
            'action' => 'penalidade',
            'user_name' => $umFilho->name,
            'detail' => "Penalidade: {$umaMissao->description}",
            'amount' => $umaMissao->coins,
        ]);

        $this->reply("Penalidade aplicada!");

        $textoSucesso = "⚠️ *Penalidade Aplicada!*\n\n👤 *{$umFilho->name}* perdeu *{$umaMissao->coins} pts* por *{$umaMissao->description}*!\n💰 Aplicado por: *{$usuarioClicou->name}*";
        
        $this->chat->edit($this->messageId)
            ->html($textoSucesso)
            ->send();
    }

    public function comprar_recompensa(): void
    {
        $recompensaId = $this->data->get('id');
        $umaRecompensa = Reward::find($recompensaId);

        if (!$umaRecompensa) {
            $this->reply("Recompensa não encontrada!");
            return;
        }

        $identificadorTelegram = $this->getTelegramUserId();
        $usuarioClicou = User::where('telegram_id', $identificadorTelegram)->first();

        if (!$usuarioClicou) {
            $this->reply("Você precisa estar cadastrado no sistema para realizar a compra!", true);
            return;
        }

        if (strtolower($usuarioClicou->role) !== 'filho') {
            $this->reply("Apenas filhos podem comprar itens na lojinha!", true);
            return;
        }

        if ($usuarioClicou->balance < $umaRecompensa->cost) {
            $this->reply("Saldo insuficiente! Você tem {$usuarioClicou->balance} pts, mas custa {$umaRecompensa->cost} pts.", true);
            return;
        }

        // Deduzir custo
        $usuarioClicou->balance -= $umaRecompensa->cost;
        $usuarioClicou->save();

        // Registrar transação
        Transaction::create([
            'action' => 'compra',
            'user_name' => $usuarioClicou->name,
            'detail' => "Comprou: {$umaRecompensa->description}",
            'amount' => -$umaRecompensa->cost,
        ]);

        $this->reply("Compra realizada!");

        $textoSucesso = "🎉 *COMPRA REALIZADA!* 🎉\n\n👤 *{$usuarioClicou->name}* comprou *{$umaRecompensa->description}* por *{$umaRecompensa->cost} pts*!\n💰 Novo saldo: *{$usuarioClicou->balance} pts*.";
        
        $this->chat->html($textoSucesso)->send();
    }
}
