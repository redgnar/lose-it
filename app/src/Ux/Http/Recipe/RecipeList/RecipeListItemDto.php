<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\RecipeList;

final readonly class RecipeListItemDto
{
    public function __construct(
        public string $id,
        public string $title,
        public bool $isFavorite,
        public int $servings,
        public ?int $caloriesPerServing,
        public string $updatedAt,
    ) {
    }
}
