<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Tailor;

use App\Domain\Enum\Aggressiveness;
use App\Domain\Enum\SaveStrategy;
use App\Domain\Enum\Servings;

final readonly class TailorRecipeCommandDto
{
    public function __construct(
        public Servings $targetServings,

        public Aggressiveness $aggressiveness,

        public bool $keepSimilar,

        public SaveStrategy $saveStrategy,
    ) {
    }
}
