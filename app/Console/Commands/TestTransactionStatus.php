<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Enums\TransactionStatus;

class TestTransactionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:transaction-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste rápido da enum TransactionStatus e criação de transações com cada status.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('--- 1. Casos e Valores da Enum TransactionStatus ---');
        foreach (TransactionStatus::cases() as $status) {
            $this->line(sprintf(
                "- %s: valor='%s', label='%s', cor='%s', icone='%s'",
                $status->name,
                $status->value,
                $status->label(),
                $status->color(),
                $status->icon()
            ));
        }

        $usuario = \App\Models\User::first() ?? \App\Models\User::factory()->create([
            'name' => 'Usuário Teste',
            'email' => 'teste@combinadinhos.test',
        ]);

        $this->newLine();
        $this->info("--- 2. Criando transações de teste com cada status (Usuário ID: {$usuario->id}) ---");
        
        foreach (TransactionStatus::cases() as $status) {
            $transacao = Transaction::create([
                'user_id' => $usuario->id,
                'action' => 'missao_cumprida',
                'user_name' => $usuario->name,
                'detail' => 'Teste com status ' . $status->label(),
                'amount' => 50,
                'status' => $status,
            ]);

            // Recarrega do banco para validar o Cast do Eloquent
            $transacaoRecarregada = Transaction::find($transacao->id);

            $this->line(sprintf(
                "✔ Criado [ID: %s] | Salvo no DB: '%s' | Cast no Model: %s (instância de %s)",
                $transacaoRecarregada->id,
                $transacaoRecarregada->getRawOriginal('status'),
                $transacaoRecarregada->status->name,
                get_class($transacaoRecarregada->status)
            ));
        }

        $this->newLine();
        $this->info('--- 3. Testando valor padrão da migration/model ---');
        $transacaoPadrao = Transaction::create([
            'user_id' => $usuario->id,
            'action' => 'recompensa_resgatada',
            'user_name' => $usuario->name,
            'detail' => 'Teste valor padrão',
            'amount' => -20,
        ]);
        $transacaoPadraoRecarregada = Transaction::find($transacaoPadrao->id);
        $this->line(sprintf(
            "✔ Transação padrão [ID: %s] | DB raw: '%s' | Status Cast: %s (%s)",
            $transacaoPadraoRecarregada->id,
            $transacaoPadraoRecarregada->getRawOriginal('status'),
            $transacaoPadraoRecarregada->status->name,
            $transacaoPadraoRecarregada->status->label()
        ));

        $this->newLine();
        $this->info('✅ Todos os testes da enum TransactionStatus passaram com sucesso!');

        return static::SUCCESS;
    }
}
