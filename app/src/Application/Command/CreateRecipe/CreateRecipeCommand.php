<?php

declare(strict_types=1);

namespace App\Application\Command\CreateRecipe;

use App\Domain\Enum\Servings;

final readonly class CreateRecipeCommand
{
    public function __construct(
        public string $title,
        public string $rawIngredients,
        public string $rawSteps,
        public Servings $servings,
    ) {
    }
}
