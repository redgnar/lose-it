<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Tailor;

use App\Ux\Http\Recipe\RecipeVersionMinimalDto;

final readonly class TailorResultDto
{
    public function __construct(
        public RecipeVersionMinimalDto $scaledVersion,
        public RecipeVersionMinimalDto $finalVersion,
        public string $status,
        public ?string $message = null,
    ) {
    }
}
