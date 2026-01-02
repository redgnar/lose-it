<?php

declare(strict_types=1);

namespace App\Application\Command\TailorRecipe;

use App\Application\Contract\SyncCommandInterface;

final readonly class TailorRecipeCommand implements SyncCommandInterface
{
    public function __construct(
        public string $recipeId,
        public int $targetServings,
        public string $aggressiveness,
        public bool $keepSimilar,
        public string $saveStrategy,
    ) {
    }
}
