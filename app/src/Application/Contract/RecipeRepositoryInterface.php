<?php

declare(strict_types=1);

namespace App\Application\Contract;

use App\Application\Dto\RecipeDetailDto;
use App\Domain\Enum\Servings;

interface RecipeRepositoryInterface
{
    public function create(
        string $userId,
        string $title,
        string $rawIngredients,
        string $rawSteps,
        Servings $servings,
    ): RecipeDetailDto;
}
