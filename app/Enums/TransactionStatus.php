<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDENTE = 'P';
    case APROVADO = 'A';
    case REJEITADO = 'R';

    public function label(): string
    {
        return match($this) {
            self::PENDENTE => 'Pendente',
            self::APROVADO => 'Aprovado',
            self::REJEITADO => 'Rejeitado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDENTE => 'warning',
            self::APROVADO => 'success',
            self::REJEITADO => 'error',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDENTE => '⏳',
            self::APROVADO => '✓',
            self::REJEITADO => '✗',
        };
    }
}
