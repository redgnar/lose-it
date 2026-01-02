<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Parse;

final readonly class ParseIngredientsCommandDto
{
    /**
     * @param array<array{id?: string, original_text: string}> $ingredients
     */
    public function __construct(
        public array $ingredients,
    ) {
    }
}
