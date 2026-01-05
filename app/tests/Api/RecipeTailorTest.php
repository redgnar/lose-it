<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Application\Contract\AiReductionServiceInterface;
use App\Infrastructure\Doctrine\Entity\Recipe;
use App\Infrastructure\Doctrine\Entity\RecipeIngredient;
use App\Infrastructure\Doctrine\Entity\RecipeVersion;
use App\Infrastructure\Doctrine\Entity\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

final class RecipeTailorTest extends WebTestCase
{
    use OpenApiValidationTrait;

    public function testTailorRecipeSuccessfully(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        // 1. Setup User and Recipe
        $email = 'test'.uniqid().'@example.com';
        $user = new User(Uuid::v7(), $email, 'google-id-'.uniqid());
        $entityManager->persist($user);

        $recipe = new Recipe(Uuid::v7(), $user, 'Pasta', 4);
        $entityManager->persist($recipe);

        $version = new RecipeVersion(Uuid::v7(), $recipe, 4, 'original');
        $version->setSteps(['Cook pasta']);
        $entityManager->persist($version);

        $ingredient = new RecipeIngredient($version, '400g Pasta');
        $ingredient->setIsParsed(true);
        $ingredient->setQuantity('400.0000');
        $entityManager->persist($ingredient);

        $recipe->setActiveVersion($version);
        $entityManager->flush();

        // 2. Mock AI Service
        $aiServiceMock = $this->createMock(AiReductionServiceInterface::class);
        $aiServiceMock->method('reduce')->willReturn([
            'ingredients' => [
                [
                    'originalText' => '200g Whole Wheat Pasta',
                    'quantity' => '200.0000',
                    'isParsed' => true,
                    'isScalable' => true,
                    'ingredientId' => null,
                    'unitId' => null,
                ],
            ],
            'steps' => ['Cook whole wheat pasta'],
        ]);

        $container->set(AiReductionServiceInterface::class, $aiServiceMock);

        // 3. Execute Request
        $content = json_encode([
            'target_servings' => 2,
            'aggressiveness' => 'medium',
            'keep_similar' => true,
            'save_strategy' => 'overwrite',
        ]);
        $this->assertIsString($content);

        $client->request('POST', sprintf('/api/recipes/%s/tailor', $recipe->getId()->toString()), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $content);

        // 4. Verify Response
        $this->assertResponseMatchesOpenApi($client, '/api/recipes/{id}/tailor', 'POST');
        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);

        /** @var array{status: string, scaledVersion: array{servings: int}, finalVersion: array{servings: int, ingredients: array<int, array{originalText: string}>}} $data */
        $data = json_decode($responseContent, true);

        $this->assertEquals('success', $data['status']);
        $this->assertEquals(2, $data['scaledVersion']['servings']);
        $this->assertEquals(2, $data['finalVersion']['servings']);
        $this->assertEquals('200g Whole Wheat Pasta', $data['finalVersion']['ingredients'][0]['originalText']);
    }

    public function testTailorRecipeParseGateFailure(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        $email = 'test'.uniqid().'@example.com';
        $user = new User(Uuid::v7(), $email, 'google-id-'.uniqid());
        $entityManager->persist($user);

        $recipe = new Recipe(Uuid::v7(), $user, 'Pasta Unparsed', 4);
        $entityManager->persist($recipe);

        $version = new RecipeVersion(Uuid::v7(), $recipe, 4, 'original');
        $entityManager->persist($version);

        // 0% parsed - we need at least 5 ingredients to have < 80% if 1 is unparsed,
        // or just 1 ingredient that is unparsed (0% < 80%)
        $ingredient = new RecipeIngredient($version, 'Some mysterious stuff');
        $ingredient->setIsParsed(false);
        $entityManager->persist($ingredient);

        $ingredient2 = new RecipeIngredient($version, 'Parsed stuff');
        $ingredient2->setIsParsed(true);
        $entityManager->persist($ingredient2);

        $version->getIngredients()->add($ingredient);
        $version->getIngredients()->add($ingredient2);

        $recipe->setActiveVersion($version);
        $entityManager->flush();

        $content = json_encode([
            'target_servings' => 2,
            'aggressiveness' => 'low',
            'keep_similar' => true,
            'save_strategy' => 'new_version',
        ]);
        $this->assertIsString($content);

        $client->request('POST', sprintf('/api/recipes/%s/tailor', $recipe->getId()->toString()), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $content);

        $this->assertResponseStatusCodeSame(422);

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);

        /** @var array{error: string} $data */
        $data = json_decode($responseContent, true);
        $this->assertStringContainsString('Minimum 80% required', $data['error']);
    }

    public function testTailorRecipeNotFound(): void
    {
        $client = static::createClient();

        // GIVEN
        $nonExistentId = Uuid::v7()->toString();
        $payload = [
            'target_servings' => 2,
            'aggressiveness' => 'medium',
            'keep_similar' => true,
            'save_strategy' => 'new_version',
        ];

        // WHEN
        $client->request('POST', sprintf('/api/recipes/%s/tailor', $nonExistentId), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], (string) json_encode($payload));

        // THEN
        $this->assertResponseStatusCodeSame(404);
        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertJsonStringEqualsJsonString((string) json_encode(['error' => 'Recipe not found.']), $responseContent);
    }

    public function testTailorRecipeQuotaExceeded(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        // GIVEN
        $email = 'test-quota'.uniqid().'@example.com';
        $user = new User(Uuid::v7(), $email, 'google-id-'.uniqid());
        $entityManager->persist($user);

        $quota = new \App\Infrastructure\Doctrine\Entity\TailoringQuota($user);
        $quota->setWeeklyAttemptsCount(5); // Max is 5
        $entityManager->persist($quota);

        $recipe = new Recipe(Uuid::v7(), $user, 'Pasta', 4);
        $entityManager->persist($recipe);

        $entityManager->flush();

        $payload = [
            'target_servings' => 2,
            'aggressiveness' => 'medium',
            'keep_similar' => true,
            'save_strategy' => 'new_version',
        ];

        // WHEN
        $client->request('POST', sprintf('/api/recipes/%s/tailor', $recipe->getId()->toString()), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], (string) json_encode($payload));

        // THEN
        $this->assertResponseStatusCodeSame(429);
        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertJsonStringEqualsJsonString((string) json_encode(['error' => 'Weekly tailoring quota exceeded.']), $responseContent);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('provideInvalidPayloads')]
    public function testTailorRecipeValidationFailures(array $payload): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        // Setup a real recipe to bypass "Recipe not found" error
        $email = 'test'.uniqid().'@example.com';
        $user = new User(Uuid::v7(), $email, 'google-id-'.uniqid());
        $entityManager->persist($user);

        $recipe = new Recipe(Uuid::v7(), $user, 'Validation Test', 4);
        $entityManager->persist($recipe);
        $entityManager->flush();

        $content = json_encode($payload);
        $this->assertIsString($content);

        $client->request('POST', sprintf('/api/recipes/%s/tailor', $recipe->getId()->toString()), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $content);

        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * @return iterable<string, array{0: array<string, mixed>}>
     */
    public static function provideInvalidPayloads(): iterable
    {
        yield 'missing all fields' => [[]];

        yield 'missing target_servings' => [[
            'aggressiveness' => 'medium',
            'keep_similar' => true,
            'save_strategy' => 'overwrite',
        ]];

        yield 'null values' => [[
            'target_servings' => null,
            'aggressiveness' => null,
            'keep_similar' => null,
            'save_strategy' => null,
        ]];

        yield 'invalid target_servings (wrong type string)' => [[
            'target_servings' => 'invalid',
            'aggressiveness' => 'medium',
            'keep_similar' => true,
            'save_strategy' => 'overwrite',
        ]];

        yield 'invalid aggressiveness (not in enum)' => [[
            'target_servings' => 4,
            'aggressiveness' => 'extreme',
            'keep_similar' => true,
            'save_strategy' => 'overwrite',
        ]];

        yield 'invalid save_strategy (wrong type bool)' => [[
            'target_servings' => 4,
            'aggressiveness' => 'medium',
            'keep_similar' => true,
            'save_strategy' => true,
        ]];
    }
}
