<?php

declare(strict_types=1);

namespace App\Application\Dto;

use App\Domain\Enum\Servings;

final readonly class RecipeVersionMinimalDto
{
    /**
     * @param RecipeIngredientDto[] $ingredients
     */
    public function __construct(
        public string $id,
        public Servings $servings,
        public array $ingredients,
    ) {
    }
}
