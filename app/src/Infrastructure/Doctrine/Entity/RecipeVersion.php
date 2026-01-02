<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\RecipeVersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RecipeVersionRepository::class)]
#[ORM\Table(name: 'recipe_versions')]
#[ORM\Index(columns: ['recipe_id', 'created_at'], name: 'idx_versions_recipe_created')]
class RecipeVersion
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Recipe $recipe;

    #[ORM\ManyToOne(targetEntity: RecipeVersion::class)]
    #[ORM\JoinColumn(name: 'parent_version_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?RecipeVersion $parentVersion = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::INTEGER)]
    private int $servings;

    /**
     * @var array<mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $steps = [];

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $totalCalories = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $caloriesPerServing = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $calorieConfidence = null;

    #[ORM\Column(length: 30)]
    private string $tailoringType;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $aiPromptVersion = null;

    /**
     * @var Collection<int, RecipeIngredient>
     */
    #[ORM\OneToMany(mappedBy: 'recipeVersion', targetEntity: RecipeIngredient::class, cascade: ['persist', 'remove'])]
    private Collection $ingredients;

    public function __construct(Uuid $id, Recipe $recipe, int $servings, string $tailoringType)
    {
        $this->id = $id;
        $this->recipe = $recipe;
        $this->servings = $servings;
        $this->tailoringType = $tailoringType;
        $this->createdAt = new \DateTimeImmutable();
        $this->ingredients = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function getParentVersion(): ?RecipeVersion
    {
        return $this->parentVersion;
    }

    public function setParentVersion(?RecipeVersion $parentVersion): void
    {
        $this->parentVersion = $parentVersion;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getServings(): int
    {
        return $this->servings;
    }

    public function setServings(int $servings): void
    {
        $this->servings = $servings;
    }

    /**
     * @return array<mixed>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * @param array<mixed> $steps
     */
    public function setSteps(array $steps): void
    {
        $this->steps = $steps;
    }

    public function getTotalCalories(): ?int
    {
        return $this->totalCalories;
    }

    public function setTotalCalories(?int $totalCalories): void
    {
        $this->totalCalories = $totalCalories;
    }

    public function getCaloriesPerServing(): ?int
    {
        return $this->caloriesPerServing;
    }

    public function setCaloriesPerServing(?int $caloriesPerServing): void
    {
        $this->caloriesPerServing = $caloriesPerServing;
    }

    public function getCalorieConfidence(): ?string
    {
        return $this->calorieConfidence;
    }

    public function setCalorieConfidence(?string $calorieConfidence): void
    {
        $this->calorieConfidence = $calorieConfidence;
    }

    public function getTailoringType(): string
    {
        return $this->tailoringType;
    }

    public function setTailoringType(string $tailoringType): void
    {
        $this->tailoringType = $tailoringType;
    }

    public function getAiPromptVersion(): ?string
    {
        return $this->aiPromptVersion;
    }

    public function setAiPromptVersion(?string $aiPromptVersion): void
    {
        $this->aiPromptVersion = $aiPromptVersion;
    }

    /**
     * @return Collection<int, RecipeIngredient>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }
}
