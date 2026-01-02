<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\RecipeDetail;

use App\Application\Dto\RecipeIngredientDto;

final readonly class RecipeDetailDto
{
    /**
     * @param RecipeIngredientDto[] $ingredients
     * @param string[]              $steps
     */
    public function __construct(
        public string $id,
        public string $title,
        public bool $isFavorite,
        public string $versionId,
        public int $servings,
        public array $ingredients,
        public array $steps,
        public ?int $totalCalories,
        public ?int $caloriesPerServing,
        public ?string $calorieConfidence,
    ) {
    }
}
