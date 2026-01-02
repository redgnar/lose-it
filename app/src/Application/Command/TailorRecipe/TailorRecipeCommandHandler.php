<?php

declare(strict_types=1);

namespace App\Application\Command\TailorRecipe;

use App\Application\Contract\AiReductionServiceInterface;
use App\Application\Contract\QuotaServiceInterface;
use App\Application\Contract\TailoringServiceInterface;
use App\Domain\Contract\ScalingServiceInterface;

final readonly class TailorRecipeCommandHandler
{
    public function __construct(
        private QuotaServiceInterface $quotaService,
        private TailoringServiceInterface $tailoringService,
        private ScalingServiceInterface $scalingService,
        private AiReductionServiceInterface $aiReductionService,
    ) {
    }

    public function __invoke(TailorRecipeCommand $command): void
    {
        $this->quotaService->checkAndIncrementQuota($command->recipeId);

        // Logic will be implemented in next steps
        echo count([
            $this->tailoringService,
            $this->scalingService,
            $this->aiReductionService,
        ]);
    }
}
