<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Tailor;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class TailorRecipeCommandDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Choice(callback: [\App\Domain\Enum\Servings::class, 'values'], message: 'Invalid target servings')]
        #[OA\Property(description: 'Target servings for the tailored recipe', type: 'integer', example: 4)]
        public int $target_servings,

        #[Assert\NotNull]
        #[Assert\Choice(callback: [\App\Domain\Enum\Aggressiveness::class, 'values'], message: 'Invalid aggressiveness')]
        #[OA\Property(description: 'Aggressiveness level for the tailoring', type: 'string', example: 'medium')]
        public string $aggressiveness,

        #[Assert\NotNull]
        #[OA\Property(description: 'Whether to keep ingredients similar to original', type: 'boolean', example: true)]
        public bool $keep_similar,

        #[Assert\NotNull]
        #[Assert\Choice(callback: [\App\Domain\Enum\SaveStrategy::class, 'values'], message: 'Invalid save strategy')]
        #[OA\Property(description: 'Strategy for saving the tailored recipe', type: 'string', example: 'new_version')]
        public string $save_strategy,
    ) {
    }
}
