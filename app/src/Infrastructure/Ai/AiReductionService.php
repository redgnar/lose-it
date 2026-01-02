<?php

declare(strict_types=1);

namespace App\Infrastructure\Ai;

use App\Application\Contract\AiReductionServiceInterface;
use App\Domain\Enum\Aggressiveness;

final readonly class AiReductionService implements AiReductionServiceInterface
{
    /**
     * @param array<string, mixed> $scaledVersionData
     *
     * @return array<string, mixed>
     */
    public function reduce(array $scaledVersionData, Aggressiveness $aggressiveness, bool $keepSimilar): array
    {
        // TODO: Implement AI reduction logic
        return $scaledVersionData;
    }
}
