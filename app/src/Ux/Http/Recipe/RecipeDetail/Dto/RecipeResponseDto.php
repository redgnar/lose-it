<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\RecipeDetail\Dto;

use App\Application\Dto\RecipeDetailDto;
use App\Ux\Http\Recipe\Tailor\Dto\RecipeIngredientResponseDto;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class RecipeResponseDto implements \JsonSerializable
{
    /**
     * @param RecipeIngredientResponseDto[] $ingredients
     * @param string[]                      $steps
     */
    public function __construct(
        #[OA\Property(description: 'ID of the recipe', type: 'string', format: 'uuid', example: '018f3b4a-1234-7890-abcd-1234567890ab')]
        public string $id,
        #[OA\Property(description: 'Title of the recipe', type: 'string', example: 'Spaghetti Carbonara')]
        public string $title,
        #[SerializedName('is_favorite')]
        #[OA\Property(description: 'Whether the recipe is a favorite', type: 'boolean', example: false)]
        public bool $isFavorite,
        #[SerializedName('version_id')]
        #[OA\Property(description: 'ID of the active version', type: 'string', format: 'uuid', example: '018f3b4a-1234-7890-abcd-1234567890ab')]
        public string $versionId,
        #[OA\Property(description: 'Number of servings', type: 'integer', example: 2)]
        public int $servings,
        #[OA\Property(description: 'List of ingredients', type: 'array', items: new OA\Items(ref: new Model(type: RecipeIngredientResponseDto::class)))]
        public array $ingredients,
        #[OA\Property(description: 'List of cooking steps', type: 'array', items: new OA\Items(type: 'string'))]
        public array $steps,
        #[SerializedName('total_calories')]
        #[OA\Property(description: 'Total calories for the recipe', type: 'integer', nullable: true, example: 1200)]
        public ?int $totalCalories,
        #[SerializedName('calories_per_serving')]
        #[OA\Property(description: 'Calories per serving', type: 'integer', nullable: true, example: 300)]
        public ?int $caloriesPerServing,
        #[SerializedName('calorie_confidence')]
        #[OA\Property(description: 'Confidence level of the calorie calculation', type: 'string', nullable: true, example: 'high')]
        public ?string $calorieConfidence,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'is_favorite' => $this->isFavorite,
            'version_id' => $this->versionId,
            'servings' => $this->servings,
            'ingredients' => $this->ingredients,
            'steps' => $this->steps,
            'total_calories' => $this->totalCalories,
            'calories_per_serving' => $this->caloriesPerServing,
            'calorie_confidence' => $this->calorieConfidence,
        ];
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
