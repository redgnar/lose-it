<?php

declare(strict_types=1);

namespace App\Application\Command\UpdateRecipe;

final readonly class UpdateRecipeCommand
{
    public function __construct(
        public ?bool $isFavorite = null,
        public ?string $title = null,
    ) {
    }
}
