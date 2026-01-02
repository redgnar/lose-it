<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\UserPreferenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPreferenceRepository::class)]
#[ORM\Table(name: 'user_preferences')]
class UserPreference
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'preferences', targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $targetCaloriesPerServing = null;

    #[ORM\Column(type: 'string', length: 10, options: ['default' => 'metric'])]
    private string $unitSystem = 'metric';

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTargetCaloriesPerServing(): ?int
    {
        return $this->targetCaloriesPerServing;
    }

    public function setTargetCaloriesPerServing(?int $targetCaloriesPerServing): void
    {
        $this->targetCaloriesPerServing = $targetCaloriesPerServing;
    }

    public function getUnitSystem(): string
    {
        return $this->unitSystem;
    }

    public function setUnitSystem(string $unitSystem): void
    {
        $this->unitSystem = $unitSystem;
    }
}
