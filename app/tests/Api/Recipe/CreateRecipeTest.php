<?php

declare(strict_types=1);

namespace App\Tests\Api\Recipe;

use App\Infrastructure\Doctrine\Entity\User;
use App\Tests\Api\OpenApiValidationTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

final class CreateRecipeTest extends WebTestCase
{
    use OpenApiValidationTrait;

    public function testCreateRecipeSuccessfully(): void
    {
        // GIVEN
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        $email = 'chef'.uniqid().'@example.com';
        $user = new User(Uuid::v7(), $email, 'google-id-'.uniqid());
        $entityManager->persist($user);
        $entityManager->flush();

        // Simulate authentication
        $client->loginUser($user);

        $payload = [
            'title' => 'Spaghetti Carbonara',
            'raw_ingredients' => "200g Pasta\n2 Eggs\n50g Guanciale",
            'raw_steps' => "1. Boil water\n2. Cook pasta\n3. Mix everything",
            'servings' => 2,
        ];

        // WHEN
        $client->request('POST', '/api/recipes', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], (string) json_encode($payload));

        // THEN
        $this->assertResponseStatusCodeSame(201);

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        /** @var array{title: string, servings: int, ingredients: array<int, array{original_text: string}>, steps: array<int, string>, is_favorite: bool, id: string, version_id: string} $data */
        $data = json_decode($responseContent, true);

        $this->assertEquals('Spaghetti Carbonara', $data['title']);
        $this->assertEquals(2, $data['servings']);
        $this->assertCount(3, $data['ingredients']);
        $this->assertEquals('200g Pasta', $data['ingredients'][0]['original_text']);
        $this->assertCount(3, $data['steps']);
        $this->assertEquals('1. Boil water', $data['steps'][0]);
        $this->assertFalse($data['is_favorite']);
        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['version_id']);

        // Verify Database
        $entityManager->clear(); // Clear identity map to force reload from DB
        /** @var \App\Infrastructure\Doctrine\Entity\Recipe|null $recipe */
        $recipe = $entityManager->getRepository(\App\Infrastructure\Doctrine\Entity\Recipe::class)->find(Uuid::fromString($data['id']));
        $this->assertNotNull($recipe);
        $this->assertEquals('Spaghetti Carbonara', $recipe->getTitle());

        $activeVersion = $recipe->getActiveVersion();
        $this->assertNotNull($activeVersion);
        $this->assertCount(3, $activeVersion->getIngredients());
        $ingredient = $activeVersion->getIngredients()->first();
        $this->assertInstanceOf(\App\Infrastructure\Doctrine\Entity\RecipeIngredient::class, $ingredient);
        $this->assertEquals('200g Pasta', $ingredient->getOriginalText());
    }

    public function testCreateRecipeValidationFailure(): void
    {
        // GIVEN
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        $user = new User(Uuid::v7(), 'test'.uniqid().'@example.com', 'google-id-'.uniqid());
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);

        $testCases = [
            'empty_title' => [
                'payload' => [
                    'title' => '',
                    'raw_ingredients' => 'Pasta',
                    'raw_steps' => 'Cook',
                    'servings' => 2,
                ],
                'expected_violation' => 'title',
            ],
            'empty_ingredients' => [
                'payload' => [
                    'title' => 'Spaghetti',
                    'raw_ingredients' => '',
                    'raw_steps' => 'Cook',
                    'servings' => 2,
                ],
                'expected_violation' => 'raw_ingredients',
            ],
            'empty_steps' => [
                'payload' => [
                    'title' => 'Spaghetti',
                    'raw_ingredients' => 'Pasta',
                    'raw_steps' => '',
                    'servings' => 2,
                ],
                'expected_violation' => 'raw_steps',
            ],
            'invalid_servings' => [
                'payload' => [
                    'title' => 'Spaghetti',
                    'raw_ingredients' => 'Pasta',
                    'raw_steps' => 'Cook',
                    'servings' => 99,
                ],
                'expected_status' => 422,
            ],
            'too_long_ingredients' => [
                'payload' => [
                    'title' => 'Spaghetti',
                    'raw_ingredients' => str_repeat('a', 10001),
                    'raw_steps' => 'Cook',
                    'servings' => 2,
                ],
                'expected_status' => 422,
            ],
            'too_long_steps' => [
                'payload' => [
                    'title' => 'Spaghetti',
                    'raw_ingredients' => 'Pasta',
                    'raw_steps' => str_repeat('a', 10001),
                    'servings' => 2,
                ],
                'expected_status' => 422,
            ],
        ];

        foreach ($testCases as $name => $case) {
            // WHEN
            $client->request('POST', '/api/recipes', [], [], [
                'CONTENT_TYPE' => 'application/json',
            ], (string) json_encode($case['payload']));

            // THEN
            $this->assertResponseStatusCodeSame($case['expected_status'] ?? 422, "Test case $name failed");
        }
    }

    public function testCreateRecipeUnauthorized(): void
    {
        // GIVEN
        $client = static::createClient();

        $payload = [
            'title' => 'Spaghetti Carbonara',
            'raw_ingredients' => 'Pasta',
            'raw_steps' => 'Cook',
            'servings' => 2,
        ];

        // WHEN
        $client->request('POST', '/api/recipes', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], (string) json_encode($payload));

        // THEN
        $this->assertResponseStatusCodeSame(401);
    }
}
