<?php

declare(strict_types=1);

namespace App\Application\Command\CreateRecipe;

use App\Application\Contract\RecipeRepositoryInterface;
use App\Application\Dto\RecipeDetailDto;

final readonly class CreateRecipeCommandHandler
{
    public function __construct(
        private RecipeRepositoryInterface $recipeRepository,
    ) {
    }

    public function __invoke(CreateRecipeCommand $command): RecipeDetailDto
    {
        return $this->recipeRepository->create(
            $command->userId,
            $command->title,
            $command->rawIngredients,
            $command->rawSteps,
            $command->servings
        );
    }
}
