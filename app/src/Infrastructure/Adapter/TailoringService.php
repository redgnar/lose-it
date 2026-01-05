<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapter;

use App\Application\Contract\AiReductionServiceInterface;
use App\Application\Contract\TailoringServiceInterface;
use App\Application\Dto\RecipeIngredientDto;
use App\Application\Dto\RecipeVersionMinimalDto;
use App\Application\Dto\TailorResultDto;
use App\Application\Exception\ParseGateException;
use App\Application\Exception\RecipeNotFoundException;
use App\Domain\Contract\ScalingServiceInterface;
use App\Domain\Enum\Aggressiveness;
use App\Domain\Enum\SaveStrategy;
use App\Domain\Enum\Servings;
use App\Domain\Model\RecipeIngredient as DomainIngredient;
use App\Domain\Model\RecipeVersion as DomainVersion;
use App\Domain\ValueObject\RecipeIngredientCollection;
use App\Domain\ValueObject\RecipeStepCollection;
use App\Infrastructure\Doctrine\Entity\Recipe;
use App\Infrastructure\Doctrine\Entity\RecipeIngredient;
use App\Infrastructure\Doctrine\Entity\RecipeVersion;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class TailoringService implements TailoringServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ScalingServiceInterface $scalingService,
        private AiReductionServiceInterface $aiReductionService,
    ) {
    }

    public function tailor(
        string $recipeId,
        Servings $targetServings,
        Aggressiveness $aggressiveness,
        bool $keepSimilar,
        SaveStrategy $saveStrategy,
    ): TailorResultDto {
        $recipe = $this->entityManager->find(Recipe::class, $recipeId);
        if (!$recipe instanceof Recipe) {
            throw new RecipeNotFoundException('Recipe not found.');
        }

        $activeVersion = $recipe->getActiveVersion();
        if (!$activeVersion instanceof RecipeVersion) {
            throw new RecipeNotFoundException('Recipe has no active version.');
        }

        // 0. Parse Gate
        $this->verifyParseGate($activeVersion);

        // 1. Convert Doctrine Entity to Domain Model
        $domainVersion = $this->toDomain($activeVersion);

        // 2. Perform Scaling (Domain Layer)
        $scaledDomainVersion = $this->scalingService->scale($domainVersion, $targetServings);

        // 3. Persist Scaled Version (Infrastructure Layer)
        $scaledEntity = $this->toEntity($scaledDomainVersion, $recipe, $activeVersion, 'scale');
        $this->entityManager->persist($scaledEntity);

        // 4. AI Reduction
        $avoidList = [];
        foreach ($recipe->getUser()->getAvoidList() as $avoidIngredient) {
            $avoidList[] = $avoidIngredient->getName();
        }

        $targetCalories = $recipe->getUser()->getPreferences()?->getTargetCaloriesPerServing();

        $scaledData = [
            'ingredients' => $this->ingredientsToData($scaledDomainVersion->ingredients),
            'steps' => $scaledDomainVersion->steps->toArray(),
        ];

        /** @var array{ingredients: array<int, mixed>, steps: array<int, string>} $reducedData */
        $reducedData = $this->aiReductionService->reduce(
            $scaledData,
            $aggressiveness,
            $keepSimilar,
            $avoidList,
            $targetCalories
        );

        $status = 'success';
        $message = null;

        if ($reducedData === $scaledData) {
            $finalEntity = $scaledEntity;
            if (Aggressiveness::LOW !== $aggressiveness) {
                $status = 'partial_success';
                $message = 'Calories unavailable; reduction skipped due to timeout or error';
            }
        } else {
            // Convert reduced data back to Domain then to Entity
            $reducedDomainVersion = new DomainVersion(
                Uuid::v7(),
                $domainVersion->recipeId,
                $targetServings,
                $this->dataToIngredients($reducedData['ingredients']),
                new RecipeStepCollection(...$reducedData['steps']),
                null,
                null
            );

            $finalEntity = $this->toEntity($reducedDomainVersion, $recipe, $scaledEntity, 'calorie_reduction');
            $this->entityManager->persist($finalEntity);
        }

        // 5. Update Recipe if needed
        if (SaveStrategy::OVERWRITE === $saveStrategy) {
            $recipe->setActiveVersion($finalEntity);
            $recipe->setServings($finalEntity->getServings());
            $recipe->setTotalCalories($finalEntity->getTotalCalories());
            $recipe->setCaloriesPerServing($finalEntity->getCaloriesPerServing());
        }

        $this->entityManager->flush();

        return new TailorResultDto(
            new RecipeVersionMinimalDto(
                $scaledEntity->getId()->toString(),
                Servings::from($scaledEntity->getServings()),
                $this->entityIngredientsToDto($scaledEntity->getIngredients())
            ),
            new RecipeVersionMinimalDto(
                $finalEntity->getId()->toString(),
                Servings::from($finalEntity->getServings()),
                $this->entityIngredientsToDto($finalEntity->getIngredients())
            ),
            $status,
            $message
        );
    }

    private function verifyParseGate(RecipeVersion $version): void
    {
        $ingredients = $version->getIngredients();
        $totalIngredients = $ingredients->count();
        if (0 === $totalIngredients) {
            return;
        }

        $parsedCount = 0;
        $unparsedList = [];

        foreach ($ingredients as $ingredient) {
            if ($ingredient->isParsed()) {
                ++$parsedCount;
            } else {
                $unparsedList[] = $ingredient->getOriginalText();
            }
        }

        $parseRate = ($parsedCount / $totalIngredients) * 100;

        if ($parseRate < 80) {
            throw new ParseGateException(sprintf('Only %.1f%% of ingredients are parsed. Minimum 80%% required.', $parseRate), $unparsedList);
        }
    }

    /**
     * @return array<int, array{originalText: string, quantity: ?string, isParsed: bool, isScalable: bool, ingredientId: string|int|null, unitId: ?string}>
     */
    private function ingredientsToData(RecipeIngredientCollection $ingredients): array
    {
        $data = [];
        foreach ($ingredients as $ingredient) {
            $data[] = [
                'originalText' => $ingredient->originalText,
                'quantity' => $ingredient->quantity,
                'isParsed' => $ingredient->isParsed,
                'isScalable' => $ingredient->isScalable,
                'ingredientId' => $ingredient->ingredientId,
                'unitId' => $ingredient->unitId,
            ];
        }

        return $data;
    }

    /**
     * @param array<int, mixed> $data
     */
    private function dataToIngredients(array $data): RecipeIngredientCollection
    {
        $ingredients = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            $ingredients[] = new DomainIngredient(
                isset($item['originalText']) && is_string($item['originalText']) ? $item['originalText'] : '',
                isset($item['quantity']) && (is_string($item['quantity']) || is_numeric($item['quantity'])) ? (string) $item['quantity'] : null,
                isset($item['isParsed']) && is_bool($item['isParsed']) ? $item['isParsed'] : false,
                isset($item['isScalable']) && is_bool($item['isScalable']) ? $item['isScalable'] : true,
                isset($item['ingredientId']) && (is_string($item['ingredientId']) || is_int($item['ingredientId'])) ? (string) $item['ingredientId'] : null,
                isset($item['unitId']) && is_string($item['unitId']) ? $item['unitId'] : null
            );
        }

        return new RecipeIngredientCollection(...$ingredients);
    }

    /**
     * @param Collection<int, RecipeIngredient> $ingredients
     *
     * @return RecipeIngredientDto[]
     */
    private function entityIngredientsToDto(Collection $ingredients): array
    {
        $dtos = [];
        foreach ($ingredients as $ingredient) {
            $dtos[] = new RecipeIngredientDto(
                null,
                $ingredient->getOriginalText(),
                $ingredient->getQuantity(),
                $ingredient->getUnit()?->getId(),
                $ingredient->getIngredient()?->getName(),
                $ingredient->isParsed()
            );
        }

        return $dtos;
    }

    private function toDomain(RecipeVersion $version): DomainVersion
    {
        $ingredients = [];
        foreach ($version->getIngredients() as $ingredient) {
            $ingredients[] = new DomainIngredient(
                $ingredient->getOriginalText(),
                $ingredient->getQuantity(),
                $ingredient->isParsed(),
                $ingredient->isScalable(),
                $ingredient->getIngredient()?->getId(),
                $ingredient->getUnit()?->getId()
            );
        }

        return new DomainVersion(
            $version->getId(),
            $version->getRecipe()->getId(),
            Servings::from($version->getServings()),
            new RecipeIngredientCollection(...$ingredients),
            new RecipeStepCollection(...$version->getSteps()),
            $version->getTotalCalories(),
            $version->getCaloriesPerServing()
        );
    }

    private function toEntity(DomainVersion $domainVersion, Recipe $recipe, RecipeVersion $parent, string $type): RecipeVersion
    {
        $version = new RecipeVersion(
            $domainVersion->id,
            $recipe,
            $domainVersion->servings->value,
            $type
        );
        $version->setParentVersion($parent);
        $version->setSteps($domainVersion->steps->toArray());
        $version->setTotalCalories($domainVersion->totalCalories);
        $version->setCaloriesPerServing($domainVersion->caloriesPerServing);

        foreach ($domainVersion->ingredients as $domainIngredient) {
            $ingredient = new RecipeIngredient($version, $domainIngredient->originalText);
            $ingredient->setQuantity($domainIngredient->quantity);
            $ingredient->setIsParsed($domainIngredient->isParsed);
            $ingredient->setIsScalable($domainIngredient->isScalable);

            if (null !== $domainIngredient->ingredientId) {
                $ingredient->setIngredient($this->entityManager->getReference(
                    \App\Infrastructure\Doctrine\Entity\IngredientRegistry::class,
                    (string) $domainIngredient->ingredientId
                ));
            }

            if (null !== $domainIngredient->unitId) {
                $ingredient->setUnit($this->entityManager->getReference(
                    \App\Infrastructure\Doctrine\Entity\UnitRegistry::class,
                    $domainIngredient->unitId
                ));
            }

            $version->getIngredients()->add($ingredient);
        }

        return $version;
    }
}
