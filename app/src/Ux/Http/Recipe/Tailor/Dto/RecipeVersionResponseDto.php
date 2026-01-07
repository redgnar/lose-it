<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Tailor\Dto;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

final readonly class RecipeVersionResponseDto implements \JsonSerializable
{
    /**
     * @param RecipeIngredientResponseDto[] $ingredients
     */
    public function __construct(
        #[OA\Property(description: 'ID of the recipe version', type: 'string', format: 'uuid', example: '018f3b4a-1234-7890-abcd-1234567890ab')]
        public string $id,
        #[OA\Property(description: 'Number of servings', type: 'integer', example: 4)]
        public int $servings,
        #[OA\Property(
            description: 'List of ingredients',
            type: 'array',
            items: new OA\Items(ref: new Model(type: RecipeIngredientResponseDto::class))
        )]
        public array $ingredients,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'servings' => $this->servings,
            'ingredients' => $this->ingredients,
        ];
    }

    public static function fromApplicationDto(\App\Application\Dto\RecipeVersionMinimalDto $dto): self
    {
        return new self(
            $dto->id,
            $dto->servings->value,
            array_map(fn ($i) => RecipeIngredientResponseDto::fromApplicationDto($i), $dto->ingredients)
        );
    }
}
