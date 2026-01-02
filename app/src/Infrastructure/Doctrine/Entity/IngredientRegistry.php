<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\IngredientRegistryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientRegistryRepository::class)]
#[ORM\Table(name: 'ingredient_registry')]
#[ORM\Index(columns: ['name'], name: 'idx_ingredient_name')]
class IngredientRegistry
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    /** @phpstan-ignore property.unusedType */
    private ?string $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $name;

    #[ORM\Column(length: 20, options: ['default' => 'global'])]
    private string $visibility = 'global';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isVerified = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?string $caloriesPer100g = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $externalApiId = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdByUser = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): void
    {
        $this->isVerified = $isVerified;
    }

    public function getCaloriesPer100g(): ?string
    {
        return $this->caloriesPer100g;
    }

    public function setCaloriesPer100g(?string $caloriesPer100g): void
    {
        $this->caloriesPer100g = $caloriesPer100g;
    }

    public function getExternalApiId(): ?string
    {
        return $this->externalApiId;
    }

    public function setExternalApiId(?string $externalApiId): void
    {
        $this->externalApiId = $externalApiId;
    }

    public function getCreatedByUser(): ?User
    {
        return $this->createdByUser;
    }

    public function setCreatedByUser(?User $createdByUser): void
    {
        $this->createdByUser = $createdByUser;
    }
}
