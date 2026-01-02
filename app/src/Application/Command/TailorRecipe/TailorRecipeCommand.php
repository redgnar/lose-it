<?php

declare(strict_types=1);

namespace App\Application\Command\TailorRecipe;

final readonly class TailorRecipeCommand
{
    public function __construct(
        public int $targetServings,
        public string $aggressiveness,
        public bool $keepSimilar,
        public string $saveStrategy,
    ) {
    }
}
