<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\CreateRecipe;

use App\Application\Command\CreateRecipe\CreateRecipeCommand;
use App\Application\Command\CreateRecipe\CreateRecipeCommandHandler;
use App\Infrastructure\Doctrine\Entity\User;
use App\Ux\Http\Recipe\RecipeDetail\Dto\RecipeResponseDto;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class CreateRecipeController extends AbstractController
{
    #[Route('/api/recipes', name: 'api_recipe_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/recipes',
        description: 'Create a new recipe.',
        summary: 'Create a recipe',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: new Model(type: CreateRecipeCommandDto::class))
        ),
        tags: ['Recipe'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Recipe created successfully',
                content: new OA\JsonContent(ref: new Model(type: RecipeResponseDto::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed'
            ),
        ]
    )]
    public function __invoke(
        #[MapRequestPayload] CreateRecipeCommandDto $dto,
        #[CurrentUser] ?User $user,
        CreateRecipeCommandHandler $handler,
    ): JsonResponse {
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $command = new CreateRecipeCommand(
                $dto->title,
                $dto->rawIngredients,
                $dto->rawSteps,
                \App\Domain\Enum\Servings::from($dto->servings),
                $user->getId()->toString()
            );

            $result = $handler($command);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(RecipeResponseDto::fromApplicationDto($result), Response::HTTP_CREATED);
    }
}
