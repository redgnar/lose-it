<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\RecipeVersionList;

final readonly class RecipeVersionListItemDto
{
    public function __construct(
        public string $id,
        public int $servings,
        public string $createdAt,
        public ?string $parentVersionId,
        public string $type, // original, tailored
    ) {
    }
}
