<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Application\Contract\RecipeRepositoryInterface;
use App\Application\Dto\RecipeDetailDto;
use App\Application\Dto\RecipeIngredientDto;
use App\Domain\Enum\Servings;
use App\Infrastructure\Doctrine\Entity\Recipe;
use App\Infrastructure\Doctrine\Entity\RecipeIngredient;
use App\Infrastructure\Doctrine\Entity\RecipeVersion;
use App\Infrastructure\Doctrine\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository implements RecipeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    public function create(
        string $userId,
        string $title,
        string $rawIngredients,
        string $rawSteps,
        Servings $servings,
    ): RecipeDetailDto {
        $entityManager = $this->getEntityManager();
        $user = $entityManager->getRepository(User::class)->find(Uuid::fromString($userId));
        if (!$user instanceof User) {
            throw new \RuntimeException('User not found');
        }

        $recipe = new Recipe(
            Uuid::v7(),
            $user,
            $title,
            $servings->value
        );

        $version = new RecipeVersion(
            Uuid::v7(),
            $recipe,
            $servings->value,
            'original'
        );

        // Parse ingredients
        $rawIngredientsArray = explode("\n", $rawIngredients);
        $ingredients = [];
        $ingredientDtos = [];
        foreach ($rawIngredientsArray as $rawIngredient) {
            $rawIngredient = trim($rawIngredient);
            if (empty($rawIngredient)) {
                continue;
            }
            $ingredient = new RecipeIngredient($version, $rawIngredient);
            $entityManager->persist($ingredient);
            $ingredients[] = $ingredient;

            $ingredientDtos[] = new RecipeIngredientDto(
                null,
                $rawIngredient,
                null,
                null,
                null,
                false
            );
        }

        // Parse steps
        $steps = array_values(array_filter(array_map('trim', explode("\n", $rawSteps))));
        $version->setSteps($steps);

        $recipe->setActiveVersion($version);

        // Search text update
        $searchTextParts = [$recipe->getTitle()];
        foreach ($ingredients as $ing) {
            $searchTextParts[] = $ing->getOriginalText();
        }
        $recipe->setSearchText(implode(' ', $searchTextParts));

        $entityManager->persist($recipe);
        $entityManager->persist($version);
        $entityManager->flush();

        return new RecipeDetailDto(
            $recipe->getId()->toString(),
            $recipe->getTitle(),
            $recipe->isFavorite(),
            $version->getId()->toString(),
            $servings,
            $ingredientDtos,
            $steps,
            $recipe->getTotalCalories(),
            $recipe->getCaloriesPerServing(),
            $recipe->getCalorieConfidence()
        );
    }
}
