<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapter;

use App\Application\Contract\FindRecipeServiceInterface;
use App\Application\Dto\RecipeDto;
use App\Infrastructure\Doctrine\Entity\Recipe;
use App\Infrastructure\Doctrine\Repository\RecipeRepository;

final readonly class FindRecipeService implements FindRecipeServiceInterface
{
    public function __construct(
        private RecipeRepository $recipeRepository,
    ) {
    }

    public function find(string $id): ?RecipeDto
    {
        $recipe = $this->recipeRepository->find($id);

        if (!$recipe instanceof Recipe) {
            return null;
        }

        return new RecipeDto(
            $recipe->getId()->toString(),
            $recipe->getUser()->getId()->toString(),
            $recipe->getTitle(),
            $recipe->getServings(),
        );
    }
}
