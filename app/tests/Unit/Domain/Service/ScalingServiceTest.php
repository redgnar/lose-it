<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Service;

use App\Domain\Contract\MeasurementKeywordsProviderInterface;
use App\Domain\Enum\Servings;
use App\Domain\Model\RecipeIngredient;
use App\Domain\Model\RecipeVersion;
use App\Domain\Service\ScalingService;
use App\Domain\ValueObject\MeasurementKeywords;
use App\Domain\ValueObject\RecipeIngredientCollection;
use App\Domain\ValueObject\RecipeStepCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ScalingServiceTest extends TestCase
{
    private ScalingService $service;

    protected function setUp(): void
    {
        $provider = $this->createMock(MeasurementKeywordsProviderInterface::class);
        $provider->method('getKeywords')->willReturn(MeasurementKeywords::createDefault());

        $this->service = new ScalingService($provider);
    }

    public function testScaleRecipeIngredients(): void
    {
        // GIVEN
        $recipeId = Uuid::v7();
        $sourceVersion = new RecipeVersion(
            Uuid::v7(),
            $recipeId,
            Servings::S4,
            new RecipeIngredientCollection(
                new RecipeIngredient(
                    originalText: '400g Chicken',
                    quantity: '400.0000',
                    isParsed: true,
                    isScalable: true,
                    ingredientId: '1'
                ),
                new RecipeIngredient(
                    originalText: 'Salt to taste',
                    quantity: null,
                    isParsed: false,
                    isScalable: true
                )
            ),
            new RecipeStepCollection('Step 1: Use 400g of chicken'),
            totalCalories: 800,
            caloriesPerServing: 200
        );

        // WHEN
        $scaledVersion = $this->service->scale($sourceVersion, Servings::S2);

        // THEN
        $this->assertEquals(Servings::S2, $scaledVersion->servings);
        $this->assertCount(2, $scaledVersion->ingredients);

        $ingredients = iterator_to_array($scaledVersion->ingredients);
        $this->assertEquals('200', $ingredients[0]->quantity);
        $this->assertEquals('200g Chicken', $ingredients[0]->originalText);
        $this->assertNull($ingredients[1]->quantity);
        $this->assertEquals('Salt to taste', $ingredients[1]->originalText);
        $this->assertEquals($scaledVersion->steps->toArray()[0], 'Step 1: Use 200g of chicken');
    }

    public function testScaleRecipeSteps(): void
    {
        // GIVEN
        $sourceVersion = new RecipeVersion(
            Uuid::v7(),
            Uuid::v7(),
            Servings::S1,
            new RecipeIngredientCollection(),
            new RecipeStepCollection(
                'Bake for 20 minutes at 180 degrees.',
                ['instruction' => 'Add 100ml of water']
            ),
            totalCalories: null,
            caloriesPerServing: null
        );

        // WHEN
        $scaledVersion = $this->service->scale($sourceVersion, Servings::S2);

        // THEN
        $steps = $scaledVersion->steps->toArray();
        $this->assertEquals('Bake for 20 minutes at 180 degrees.', $steps[0]);

        $step2 = $steps[1];
        $this->assertIsArray($step2);
        $this->assertEquals('Add 200ml of water', $step2['instruction']);
    }

    public function testScaleCalories(): void
    {
        // GIVEN
        $sourceVersion = new RecipeVersion(
            Uuid::v7(),
            Uuid::v7(),
            Servings::S4,
            new RecipeIngredientCollection(),
            new RecipeStepCollection(),
            totalCalories: 1000,
            caloriesPerServing: 250
        );

        // WHEN
        $scaledVersion = $this->service->scale($sourceVersion, Servings::S2);

        // THEN
        $this->assertEquals(500, $scaledVersion->totalCalories);
        $this->assertEquals(250, $scaledVersion->caloriesPerServing);
    }

    public function testDoNotScaleNonScalableIngredients(): void
    {
        // GIVEN
        $sourceVersion = new RecipeVersion(
            Uuid::v7(),
            Uuid::v7(),
            Servings::S4,
            new RecipeIngredientCollection(
                new RecipeIngredient(
                    originalText: '1 whole chicken',
                    quantity: '1',
                    isParsed: true,
                    isScalable: false
                )
            ),
            new RecipeStepCollection(),
            totalCalories: null,
            caloriesPerServing: null
        );

        // WHEN
        $scaledVersion = $this->service->scale($sourceVersion, Servings::S2);

        // THEN
        $ingredients = iterator_to_array($scaledVersion->ingredients);
        $this->assertEquals('1', $ingredients[0]->quantity);
        $this->assertEquals('1 whole chicken', $ingredients[0]->originalText);
    }

    public function testMultiLanguageScaling(): void
    {
        // GIVEN
        $keywords = new MeasurementKeywords(
            excludedUnits: ['min', 'stopnie'],
            stepPrefixes: ['Krok']
        );
        $provider = $this->createMock(MeasurementKeywordsProviderInterface::class);
        $provider->method('getKeywords')->willReturn($keywords);

        $polishService = new ScalingService($provider);

        $sourceVersion = new RecipeVersion(
            Uuid::v7(),
            Uuid::v7(),
            Servings::S1,
            new RecipeIngredientCollection(),
            new RecipeStepCollection(
                'Krok 1: Piecz przez 20 min w 180 stopnie.',
                'Dodaj 100ml wody'
            ),
            totalCalories: null,
            caloriesPerServing: null
        );

        // WHEN
        $scaledVersion = $polishService->scale($sourceVersion, Servings::S2);

        // THEN
        $steps = $scaledVersion->steps->toArray();
        $this->assertEquals('Krok 1: Piecz przez 20 min w 180 stopnie.', $steps[0]);
        $this->assertEquals('Dodaj 200ml wody', $steps[1]);
    }
}
