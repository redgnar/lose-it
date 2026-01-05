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

        $payload = [
            'title' => '', // Invalid
            'raw_ingredients' => 'Pasta',
            'raw_steps' => 'Cook',
            'servings' => 2,
        ];

        // WHEN
        $client->request('POST', '/api/recipes', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], (string) json_encode($payload));

        // THEN
        $this->assertResponseStatusCodeSame(422); // MapRequestPayload returns 422 by default for validation errors
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
