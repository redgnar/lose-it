<?php

declare(strict_types=1);

namespace App\Application\Command\ParseIngredients;

final readonly class ParseIngredientsCommand
{
    /**
     * @param array<array{id?: string, original_text: string}> $ingredients
     */
    public function __construct(
        public array $ingredients,
    ) {
    }
}
