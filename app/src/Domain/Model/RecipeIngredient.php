<?php

declare(strict_types=1);

namespace App\Domain\Model;

final readonly class RecipeIngredient
{
    public function __construct(
        public string $originalText,
        public ?string $quantity,
        public bool $isParsed,
        public bool $isScalable,
        public string|int|null $ingredientId = null,
        public ?string $unitId = null,
    ) {
    }
}
