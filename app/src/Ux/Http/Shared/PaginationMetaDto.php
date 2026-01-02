<?php

declare(strict_types=1);

namespace App\Ux\Http\Shared;

final readonly class PaginationMetaDto
{
    public function __construct(
        public int $totalCount,
        public int $totalPages,
    ) {
    }
}
