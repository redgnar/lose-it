<?php

declare(strict_types=1);

namespace App\Application\Dto;

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
