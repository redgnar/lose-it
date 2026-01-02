<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\RecipeIngredientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeIngredientRepository::class)]
#[ORM\Table(name: 'recipe_ingredients')]
#[ORM\Index(columns: ['recipe_version_id'], name: 'idx_ingredients_version')]
class RecipeIngredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    /** @phpstan-ignore property.unusedType */
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: RecipeVersion::class, inversedBy: 'ingredients')]
    #[ORM\JoinColumn(name: 'recipe_version_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private RecipeVersion $recipeVersion;

    #[ORM\ManyToOne(targetEntity: IngredientRegistry::class)]
    #[ORM\JoinColumn(name: 'ingredient_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?IngredientRegistry $ingredient = null;

    #[ORM\Column(length: 255)]
    private string $originalText;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 4, nullable: true)]
    private ?string $quantity = null;

    #[ORM\ManyToOne(targetEntity: UnitRegistry::class)]
    #[ORM\JoinColumn(name: 'unit_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?UnitRegistry $unit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 4, nullable: true)]
    private ?string $originalQuantity = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isParsed = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isScalable = true;

    #[ORM\ManyToOne(targetEntity: IngredientRegistry::class)]
    #[ORM\JoinColumn(name: 'substitution_for_ingredient_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?IngredientRegistry $substitutionForIngredient = null;

    public function __construct(RecipeVersion $recipeVersion, string $originalText)
    {
        $this->recipeVersion = $recipeVersion;
        $this->originalText = $originalText;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getRecipeVersion(): RecipeVersion
    {
        return $this->recipeVersion;
    }

    public function getIngredient(): ?IngredientRegistry
    {
        return $this->ingredient;
    }

    public function setIngredient(?IngredientRegistry $ingredient): void
    {
        $this->ingredient = $ingredient;
    }

    public function getOriginalText(): string
    {
        return $this->originalText;
    }

    public function setOriginalText(string $originalText): void
    {
        $this->originalText = $originalText;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(?string $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getUnit(): ?UnitRegistry
    {
        return $this->unit;
    }

    public function setUnit(?UnitRegistry $unit): void
    {
        $this->unit = $unit;
    }

    public function getOriginalQuantity(): ?string
    {
        return $this->originalQuantity;
    }

    public function setOriginalQuantity(?string $originalQuantity): void
    {
        $this->originalQuantity = $originalQuantity;
    }

    public function isParsed(): bool
    {
        return $this->isParsed;
    }

    public function setIsParsed(bool $isParsed): void
    {
        $this->isParsed = $isParsed;
    }

    public function isScalable(): bool
    {
        return $this->isScalable;
    }

    public function setIsScalable(bool $isScalable): void
    {
        $this->isScalable = $isScalable;
    }

    public function getSubstitutionForIngredient(): ?IngredientRegistry
    {
        return $this->substitutionForIngredient;
    }

    public function setSubstitutionForIngredient(?IngredientRegistry $substitutionForIngredient): void
    {
        $this->substitutionForIngredient = $substitutionForIngredient;
    }
}
