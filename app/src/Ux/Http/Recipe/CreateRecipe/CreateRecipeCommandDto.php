<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\CreateRecipe;

use App\Domain\Enum\Servings;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateRecipeCommandDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        public string $title,

        #[Assert\NotBlank]
        #[SerializedName('raw_ingredients')]
        public string $rawIngredients,

        #[Assert\NotBlank]
        #[SerializedName('raw_steps')]
        public string $rawSteps,

        #[Assert\NotNull]
        public Servings $servings,
    ) {
    }
}
