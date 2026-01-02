<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Tailor;

final readonly class TailorRecipeCommandDto
{
    public function __construct(
        public int $targetServings,

        public string $aggressiveness,

        public bool $keepSimilar,

        public string $saveStrategy,
    ) {
    }
}
