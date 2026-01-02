<?php

declare(strict_types=1);

namespace App\Ux\Http\Profile\UpdateUserProfile;

use App\Application\Dto\IngredientDto;

final readonly class UpdatePreferencesCommandDto
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
