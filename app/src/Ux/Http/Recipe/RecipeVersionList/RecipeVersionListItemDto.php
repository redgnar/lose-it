<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\RecipeVersionList;

use App\Domain\Enum\Servings;

final readonly class RecipeVersionListItemDto
{
    public function __construct(
        public string $id,
        public Servings $servings,
        public string $createdAt,
        public ?string $parentVersionId,
        public string $type, // original, tailored
    ) {
    }
}
