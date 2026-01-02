<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\UnitRegistryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnitRegistryRepository::class)]
#[ORM\Table(name: 'unit_registry')]
class UnitRegistry
{
    #[ORM\Id]
    #[ORM\Column(length: 50)]
    private string $id;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $abbreviation = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isScalable = true;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?string $abbreviation): void
    {
        $this->abbreviation = $abbreviation;
    }

    public function isScalable(): bool
    {
        return $this->isScalable;
    }

    public function setIsScalable(bool $isScalable): void
    {
        $this->isScalable = $isScalable;
    }
}
