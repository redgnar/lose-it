<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum Aggressiveness: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
}
