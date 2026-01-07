<?php

declare(strict_types=1);

namespace App\Application\Command\CreateRecipe;

use App\Domain\Enum\Servings;
use Webmozart\Assert\Assert;

final readonly class CreateRecipeCommand
{
    public function __construct(
        public string $title,
        public string $rawIngredients,
        public string $rawSteps,
        public Servings $servings,
        public string $userId,
    ) {
        Assert::notEmpty($this->title);
        Assert::maxLength($this->title, 255);
        Assert::notEmpty($this->rawIngredients);
        Assert::maxLength($this->rawIngredients, 10000);
        Assert::notEmpty($this->rawSteps);
        Assert::maxLength($this->rawSteps, 10000);
        Assert::uuid($this->userId);
    }
}
