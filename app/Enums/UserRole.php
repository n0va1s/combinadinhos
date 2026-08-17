<?php

namespace App\Enums;

enum UserRole: string
{
    case PAI = 'P';
    case MAE = 'M';
    case FILHO = 'S';
    case FILHA = 'D';
    
    public function label(): string
    {
        return match($this) {
            self::PAI => 'Pai',
            self::MAE => 'Mãe',
            self::FILHO => 'Filho',
            self::FILHA => 'Filha',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PAI => '👨‍🦳',
            self::MAE => '👩‍🦰',
            self::FILHO => '👦',
            self::FILHA => '👧',
        };
    }
}
