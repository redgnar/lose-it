<?php

declare(strict_types=1);

namespace App\Application\Contract;

use App\Application\Dto\TailorResultDto;
use App\Domain\Enum\Aggressiveness;
use App\Domain\Enum\SaveStrategy;
use App\Domain\Enum\Servings;

interface TailoringServiceInterface
{
    public function tailor(
        string $recipeId,
        Servings $targetServings,
        Aggressiveness $aggressiveness,
        bool $keepSimilar,
        SaveStrategy $saveStrategy,
    ): TailorResultDto;
}
