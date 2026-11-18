<?php

namespace App\Enum;

enum IceLevel: int
{
    case None = 1;
    case Less = 2;
    case Normal = 3;

    public function label(): string
    {
        return match ($this) {
            self::None => 'Sans glaçons',
            self::Less => 'Peu de glaçons',
            self::Normal => 'Quantité normale de glaçons',
        };
    }
}