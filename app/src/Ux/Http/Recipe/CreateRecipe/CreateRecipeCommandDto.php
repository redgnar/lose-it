<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\CreateRecipe;

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateRecipeCommandDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        #[OA\Property(description: 'Title of the recipe', type: 'string', example: 'Spaghetti Carbonara')]
        public string $title,

        #[Assert\NotBlank]
        #[Assert\Length(max: 10000)]
        #[SerializedName('raw_ingredients')]
        #[OA\Property(description: 'Raw text containing ingredients, one per line', type: 'string', example: "200g Pasta\n2 Eggs")]
        public string $rawIngredients,

        #[Assert\NotBlank]
        #[Assert\Length(max: 10000)]
        #[SerializedName('raw_steps')]
        #[OA\Property(description: 'Raw text containing cooking steps, one per line', type: 'string', example: "1. Boil water\n2. Cook pasta")]
        public string $rawSteps,

        #[Assert\NotNull]
        #[Assert\Choice(callback: [\App\Domain\Enum\Servings::class, 'values'], message: 'Invalid servings')]
        #[OA\Property(description: 'Number of servings', type: 'integer', example: 2)]
        public int $servings,
    ) {
    }
}
