<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum Servings: int
{
    case S1 = 1;
    case S2 = 2;
    case S3 = 3;
    case S4 = 4;
    case S5 = 5;
    case S6 = 6;
    case S7 = 7;
    case S8 = 8;
    case S9 = 9;
    case S10 = 10;
    case S11 = 11;
    case S12 = 12;

    /**
     * @return int[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
