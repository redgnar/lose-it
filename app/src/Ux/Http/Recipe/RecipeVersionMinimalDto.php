<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe;

use App\Application\Dto\RecipeIngredientDto;

final readonly class RecipeVersionMinimalDto
{
    /**
     * @param RecipeIngredientDto[] $ingredients
     */
    public function __construct(
        public string $id,
        public int $servings,
        public array $ingredients,
    ) {
    }
}
