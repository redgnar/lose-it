<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\CreateRecipe;

use App\Domain\Enum\Servings;

final readonly class CreateRecipeCommandDto
{
    public function __construct(
        public string $title,
        public string $rawIngredients,
        public string $rawSteps,
        public Servings $servings,
    ) {
    }
}
