<?php

declare(strict_types=1);

namespace App\Ux\Http\Profile\UserProfile;

use App\Application\Dto\IngredientDto;

final readonly class UserPreferencesDto
{
    /**
     * @param IngredientDto[] $avoidList
     */
    public function __construct(
        public ?int $targetCaloriesPerServing,
        public string $unitSystem,
        public array $avoidList,
        public bool $isProfileComplete,
    ) {
    }
}
