<?php

declare(strict_types=1);

namespace App\Ux\Http\Registry\IngredientList;

use App\Application\Dto\IngredientDto;

final readonly class IngredientListDto
{
    /**
     * @param IngredientDto[] $ingredients
     */
    public function __construct(
        public array $ingredients,
    ) {
    }
}
