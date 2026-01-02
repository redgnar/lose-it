<?php

declare(strict_types=1);

namespace App\Application\Contract;

use App\Domain\Enum\Aggressiveness;

interface AiReductionServiceInterface
{
    /**
     * @param array<string, mixed> $scaledVersionData
     *
     * @return array<string, mixed>
     */
    public function reduce(array $scaledVersionData, Aggressiveness $aggressiveness, bool $keepSimilar): array;
}
