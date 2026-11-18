<?php

namespace App\Enum;

enum SugarLevel: int
{
    case Zero = 1;
    case Quarter = 2;
    case Half = 3;
    case ThreeQuarters = 4;
    case Full = 5;

    public function label(): string
    {
        return match ($this) {
            self::Zero => '0 %',
            self::Quarter => '25 %',
            self::Half => '50 %',
            self::ThreeQuarters => '75 %',
            self::Full => '100 %',
        };
    }
}