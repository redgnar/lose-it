<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\RecipeList;

use App\Domain\Enum\Servings;

final readonly class RecipeListItemDto
{
    public function __construct(
        public string $id,
        public string $title,
        public bool $isFavorite,
        public Servings $servings,
        public ?int $caloriesPerServing,
        public string $updatedAt,
    ) {
    }
}
