<?php

declare(strict_types=1);

namespace App\Application\Command\CreateRecipe;

final readonly class CreateRecipeCommand
{
    public function __construct(
        public string $title,
        public string $rawIngredients,
        public string $rawSteps,
        public int $servings,
    ) {
    }
}
