<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\RecipeVersionList;

final readonly class RecipeVersionListDto
{
    /**
     * @param RecipeVersionListItemDto[] $items
     */
    public function __construct(
        public array $items,
    ) {
    }
}
