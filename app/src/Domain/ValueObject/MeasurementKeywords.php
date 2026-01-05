<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class MeasurementKeywords
{
    /**
     * @param string[] $excludedUnits
     * @param string[] $stepPrefixes
     */
    public function __construct(
        public array $excludedUnits,
        public array $stepPrefixes,
    ) {
    }

    public static function createDefault(): self
    {
        return new self(
            excludedUnits: [
                'min', 'minute', 'minutes',
                'hour', 'hours', 'h',
                'degree', 'degrees', 'c', 'f',
                'sec', 'second', 'seconds',
            ],
            stepPrefixes: ['Step']
        );
    }
}
