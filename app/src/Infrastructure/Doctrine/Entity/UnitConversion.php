<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\UnitConversionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnitConversionRepository::class)]
#[ORM\Table(name: 'unit_conversions')]
class UnitConversion
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: UnitRegistry::class)]
    #[ORM\JoinColumn(name: 'from_unit_id', referencedColumnName: 'id', nullable: false)]
    private UnitRegistry $fromUnit;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: UnitRegistry::class)]
    #[ORM\JoinColumn(name: 'to_unit_id', referencedColumnName: 'id', nullable: false)]
    private UnitRegistry $toUnit;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 6)]
    private string $factor;

    public function __construct(UnitRegistry $fromUnit, UnitRegistry $toUnit, string $factor)
    {
        $this->fromUnit = $fromUnit;
        $this->toUnit = $toUnit;
        $this->factor = $factor;
    }

    public function getFromUnit(): UnitRegistry
    {
        return $this->fromUnit;
    }

    public function getToUnit(): UnitRegistry
    {
        return $this->toUnit;
    }

    public function getFactor(): string
    {
        return $this->factor;
    }

    public function setFactor(string $factor): void
    {
        $this->factor = $factor;
    }
}
