<?php

declare(strict_types=1);

namespace App\Domain\Contract;

use App\Domain\Enum\Servings;
use App\Domain\Model\RecipeVersion;

interface ScalingServiceInterface
{
    public function scale(RecipeVersion $sourceVersion, Servings $targetServings): RecipeVersion;
}
