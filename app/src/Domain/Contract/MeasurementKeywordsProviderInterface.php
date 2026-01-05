<?php

declare(strict_types=1);

namespace App\Domain\Contract;

use App\Domain\ValueObject\MeasurementKeywords;

interface MeasurementKeywordsProviderInterface
{
    public function getKeywords(): MeasurementKeywords;
}
