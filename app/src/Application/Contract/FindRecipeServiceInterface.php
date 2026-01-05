<?php

declare(strict_types=1);

namespace App\Application\Contract;

use App\Application\Dto\RecipeDto;

interface FindRecipeServiceInterface
{
    public function find(string $id): ?RecipeDto;
}
