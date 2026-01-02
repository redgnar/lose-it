<?php

declare(strict_types=1);

namespace App\Application\Dto;

final readonly class RecipeIngredientDto
{
    public function __construct(
        public ?string $id,
        public string $originalText,
        public ?string $quantity,
        public ?string $unit,
        public ?string $item,
        public bool $isParsed,
    ) {
    }
}
