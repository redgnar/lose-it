<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Contract\MeasurementKeywordsProviderInterface;
use App\Domain\ValueObject\MeasurementKeywords;

final readonly class MeasurementKeywordsProvider implements MeasurementKeywordsProviderInterface
{
    public function getKeywords(): MeasurementKeywords
    {
        // For now, it returns the default English keywords.
        // In the future, this can be extended to support multiple languages based on context/user.
        return MeasurementKeywords::createDefault();
    }
}
