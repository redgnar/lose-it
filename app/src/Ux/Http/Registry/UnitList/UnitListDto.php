<?php

declare(strict_types=1);

namespace App\Ux\Http\Registry\UnitList;

use App\Application\Dto\UnitDto;

final readonly class UnitListDto
{
    /**
     * @param UnitDto[] $units
     */
    public function __construct(
        public array $units,
    ) {
    }
}
