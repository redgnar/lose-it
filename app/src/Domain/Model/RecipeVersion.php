<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Enum\Servings;
use App\Domain\ValueObject\RecipeIngredientCollection;
use App\Domain\ValueObject\RecipeStepCollection;
use Symfony\Component\Uid\Uuid;

final readonly class RecipeVersion
{
    public function __construct(
        public Uuid $id,
        public Uuid $recipeId,
        public Servings $servings,
        public RecipeIngredientCollection $ingredients,
        public RecipeStepCollection $steps,
        public ?int $totalCalories,
        public ?int $caloriesPerServing,
    ) {
    }
}
