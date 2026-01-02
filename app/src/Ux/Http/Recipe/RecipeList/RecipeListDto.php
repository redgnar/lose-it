<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\RecipeList;

use App\Ux\Http\Shared\PaginationMetaDto;

final readonly class RecipeListDto
{
    /**
     * @param RecipeListItemDto[] $items
     */
    public function __construct(
        public array $items,
        public PaginationMetaDto $meta,
    ) {
    }
}
