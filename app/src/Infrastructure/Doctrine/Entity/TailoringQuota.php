<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\TailoringQuotaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TailoringQuotaRepository::class)]
#[ORM\Table(name: 'tailoring_quotas')]
class TailoringQuota
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'quota', targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $weeklyAttemptsCount = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $lastResetAt;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->lastResetAt = new \DateTimeImmutable();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getWeeklyAttemptsCount(): int
    {
        return $this->weeklyAttemptsCount;
    }

    public function setWeeklyAttemptsCount(int $weeklyAttemptsCount): void
    {
        $this->weeklyAttemptsCount = $weeklyAttemptsCount;
    }

    public function getLastResetAt(): \DateTimeImmutable
    {
        return $this->lastResetAt;
    }

    public function setLastResetAt(\DateTimeImmutable $lastResetAt): void
    {
        $this->lastResetAt = $lastResetAt;
    }
}
