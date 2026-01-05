<?php

declare(strict_types=1);

namespace App\Application\Command\TailorRecipe;

use App\Application\Contract\SyncCommandInterface;
use App\Domain\Enum\Aggressiveness;
use App\Domain\Enum\SaveStrategy;
use App\Domain\Enum\Servings;

final readonly class TailorRecipeCommand implements SyncCommandInterface
{
    public function __construct(
        public string $recipeId,
        public string $userId,
        public Servings $targetServings,
        public Aggressiveness $aggressiveness,
        public bool $keepSimilar,
        public SaveStrategy $saveStrategy,
    ) {
    }
}
