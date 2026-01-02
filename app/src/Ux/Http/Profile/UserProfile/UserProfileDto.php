<?php

declare(strict_types=1);

namespace App\Ux\Http\Profile\UserProfile;

final readonly class UserProfileDto
{
    public function __construct(
        public string $id,
        public string $email,
        public UserPreferencesDto $preferences,
        public UserQuotaDto $quota,
    ) {
    }
}
