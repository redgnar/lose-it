<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Tailor;

use App\Application\Command\TailorRecipe\TailorRecipeCommand;
use App\Application\Command\TailorRecipe\TailorRecipeCommandHandler;
use App\Application\Exception\ParseGateException;
use App\Application\Exception\QuotaExceededException;
use App\Infrastructure\Doctrine\Entity\Recipe;
use App\Ux\Http\Recipe\Tailor\Dto\TailorResultResponseDto;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

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
        TailorRecipeCommandHandler $handler,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        try {
            $recipe = $entityManager->find(Recipe::class, $id);
            if (!$recipe instanceof Recipe) {
                return new JsonResponse(['error' => 'Recipe not found.'], Response::HTTP_NOT_FOUND);
            }

            $result = $handler(new TailorRecipeCommand(
                $id,
                $recipe->getUser()->getId()->toString(),
                \App\Domain\Enum\Servings::from($dto->target_servings),
                \App\Domain\Enum\Aggressiveness::from($dto->aggressiveness),
                $dto->keep_similar,
                \App\Domain\Enum\SaveStrategy::from($dto->save_strategy)
            ));

            return new JsonResponse(TailorResultResponseDto::fromApplicationDto($result));
        } catch (ParseGateException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'details' => $e->getDetails(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QuotaExceededException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
