<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\UpdateRecipe;

final readonly class UpdateRecipeCommandDto
{
    public function __construct(
        public ?bool $isFavorite = null,
        public ?string $title = null,
    ) {
    }
}
