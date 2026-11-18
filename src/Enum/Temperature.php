<?php

namespace App\Enum;

enum Temperature: int
{
    case Cold = 1;
    case Lukewarm = 2;
    case Hot = 3;

    public function label(): string
    {
        return match ($this) {
            self::Cold => 'Froid',
            self::Lukewarm => 'Tiède',
            self::Hot => 'Chaud',
        };
    }
}
