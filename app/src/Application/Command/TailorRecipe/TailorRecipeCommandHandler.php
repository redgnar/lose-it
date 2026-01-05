<?php

declare(strict_types=1);

namespace App\Application\Command\TailorRecipe;

use App\Application\Contract\QuotaServiceInterface;
use App\Application\Contract\TailoringServiceInterface;
use App\Application\Dto\TailorResultDto;

final readonly class TailorRecipeCommandHandler
{
    public function __construct(
        private QuotaServiceInterface $quotaService,
        private TailoringServiceInterface $tailoringService,
    ) {
    }

    public function __invoke(TailorRecipeCommand $command): TailorResultDto
    {
        $this->quotaService->checkAndIncrementQuota($command->userId);

        return $this->tailoringService->tailor(
            $command->recipeId,
            $command->targetServings,
            $command->aggressiveness,
            $command->keepSimilar,
            $command->saveStrategy
        );
    }
}
