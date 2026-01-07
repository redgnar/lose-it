<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Tailor;

use App\Application\Command\TailorRecipe\TailorRecipeCommand;
use App\Application\Command\TailorRecipe\TailorRecipeCommandHandler;
use App\Application\Contract\FindRecipeServiceInterface;
use App\Application\Dto\RecipeDto;
use App\Application\Exception\RecipeNotFoundException;
use App\Infrastructure\Doctrine\Entity\User;
use App\Ux\Http\Recipe\Tailor\Dto\TailorResultResponseDto;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class TailorRecipeController extends AbstractController
{
    #[Route('/api/recipes/{id}/tailor', name: 'api_recipe_tailor', methods: ['POST'])]
    #[OA\Post(
        path: '/api/recipes/{id}/tailor',
        description: 'Tailor a recipe based on target servings and aggressiveness.',
        summary: 'Tailor a recipe',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: new Model(type: TailorRecipeCommandDto::class))
        ),
        tags: ['Recipe'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'The recipe ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recipe tailored successfully',
                content: new OA\JsonContent(ref: new Model(type: TailorResultResponseDto::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request'
            ),
            new OA\Response(
                response: 404,
                description: 'Recipe not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Unprocessable entity'
            ),
            new OA\Response(
                response: 429,
                description: 'Quota exceeded'
            ),
        ]
    )]
    public function __invoke(
        string $id,
        #[MapRequestPayload] TailorRecipeCommandDto $dto,
        #[CurrentUser] ?User $user,
        TailorRecipeCommandHandler $handler,
        FindRecipeServiceInterface $findRecipeService,
    ): JsonResponse {
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $recipe = $findRecipeService->find($id);
        if (!$recipe instanceof RecipeDto) {
            throw new RecipeNotFoundException('Recipe not found.');
        }

        $result = $handler($this->createTailorRecipeCommand($recipe, $dto));

        return new JsonResponse(TailorResultResponseDto::fromApplicationDto($result));
    }

    private function createTailorRecipeCommand(RecipeDto $recipe, TailorRecipeCommandDto $dto): TailorRecipeCommand
    {
        return new TailorRecipeCommand(
            $recipe->id,
            $recipe->userId,
            \App\Domain\Enum\Servings::from($dto->target_servings),
            \App\Domain\Enum\Aggressiveness::from($dto->aggressiveness),
            $dto->keep_similar,
            \App\Domain\Enum\SaveStrategy::from($dto->save_strategy)
        );
    }
}
