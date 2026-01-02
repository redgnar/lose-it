<?php

declare(strict_types=1);

namespace App\Ux\Http\Profile\UserProfile;

final readonly class UserQuotaDto
{
    public function __construct(
        public int $weeklyAttemptsCount,
        public int $limit,
        public int $remaining,
        public string $resetAt,
    ) {
    }
}
