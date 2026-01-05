<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\RecipeDetail\Dto;

use App\Application\Dto\RecipeDetailDto;
use App\Ux\Http\Recipe\Tailor\Dto\RecipeIngredientResponseDto;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class RecipeResponseDto
{
    /**
     * @param RecipeIngredientResponseDto[] $ingredients
     * @param string[]                      $steps
     */
    public function __construct(
        public string $id,
        public string $title,
        #[SerializedName('is_favorite')]
        #[OA\Property(type: 'boolean')]
        public bool $isFavorite,
        #[SerializedName('version_id')]
        #[OA\Property(type: 'string', format: 'uuid')]
        public string $versionId,
        public int $servings,
        #[OA\Property(type: 'array', items: new OA\Items(ref: new Model(type: RecipeIngredientResponseDto::class)))]
        public array $ingredients,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        public array $steps,
        #[SerializedName('total_calories')]
        #[OA\Property(type: 'integer', nullable: true)]
        public ?int $totalCalories,
        #[SerializedName('calories_per_serving')]
        #[OA\Property(type: 'integer', nullable: true)]
        public ?int $caloriesPerServing,
        #[SerializedName('calorie_confidence')]
        #[OA\Property(type: 'string', nullable: true)]
        public ?string $calorieConfidence,
    ) {
    }

    public static function fromApplicationDto(RecipeDetailDto $dto): self
    {
        return new self(
            $dto->id,
            $dto->title,
            $dto->isFavorite,
            $dto->versionId,
            $dto->servings->value,
            array_map(fn ($ingredient) => RecipeIngredientResponseDto::fromApplicationDto($ingredient), $dto->ingredients),
            $dto->steps,
            $dto->totalCalories,
            $dto->caloriesPerServing,
            $dto->calorieConfidence
        );
    }
}
