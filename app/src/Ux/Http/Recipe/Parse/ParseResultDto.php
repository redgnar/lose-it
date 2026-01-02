<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Parse;

use App\Application\Dto\RecipeIngredientDto;

final readonly class ParseResultDto
{
    /**
     * @param RecipeIngredientDto[] $ingredients
     */
    public function __construct(
        public float $successRate,
        public array $ingredients,
    ) {
    }
}
