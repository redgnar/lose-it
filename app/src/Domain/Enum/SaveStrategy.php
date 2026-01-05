<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum SaveStrategy: string
{
    case OVERWRITE = 'overwrite';
    case NEW_VERSION = 'new_version';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
