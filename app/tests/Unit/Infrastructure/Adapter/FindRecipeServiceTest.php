<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Adapter;

use App\Application\Dto\RecipeDto;
use App\Infrastructure\Adapter\FindRecipeService;
use App\Infrastructure\Doctrine\Entity\Recipe;
use App\Infrastructure\Doctrine\Entity\User;
use App\Infrastructure\Doctrine\Repository\RecipeRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class FindRecipeServiceTest extends TestCase
{
    public function testFindReturnsRecipeDtoWhenRecipeExists(): void
    {
        // GIVEN
        $recipeId = Uuid::v7();
        $userId = Uuid::v7();

        $user = $this->createStub(User::class);
        $user->method('getId')->willReturn($userId);

        $recipe = $this->createStub(Recipe::class);
        $recipe->method('getId')->willReturn($recipeId);
        $recipe->method('getUser')->willReturn($user);
        $recipe->method('getTitle')->willReturn('Test Recipe');
        $recipe->method('getServings')->willReturn(4);

        $repository = $this->createMock(RecipeRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with($recipeId->toString())
            ->willReturn($recipe);

        $service = new FindRecipeService($repository);

        // WHEN
        $result = $service->find($recipeId->toString());

        // THEN
        $this->assertInstanceOf(RecipeDto::class, $result);
        $this->assertSame($recipeId->toString(), $result->id);
        $this->assertSame($userId->toString(), $result->userId);
        $this->assertSame('Test Recipe', $result->title);
        $this->assertSame(4, $result->servings);
    }

    public function testFindReturnsNullWhenRecipeDoesNotExist(): void
    {
        // GIVEN
        $recipeId = Uuid::v7()->toString();

        $repository = $this->createMock(RecipeRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with($recipeId)
            ->willReturn(null);

        $service = new FindRecipeService($repository);

        // WHEN
        $result = $service->find($recipeId);

        // THEN
        $this->assertNull($result);
    }
}
