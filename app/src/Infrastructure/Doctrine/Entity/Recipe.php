<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\Table(name: 'recipes')]
#[ORM\Index(columns: ['user_id', 'updated_at'], name: 'idx_recipes_user_updated')]
class Recipe
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'recipes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\OneToOne(targetEntity: RecipeVersion::class)]
    #[ORM\JoinColumn(name: 'active_version_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?RecipeVersion $activeVersion = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isFavorite = false;

    #[ORM\Column(type: Types::TEXT)]
    private string $searchText;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::INTEGER)]
    private int $servings;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $totalCalories = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $caloriesPerServing = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $calorieConfidence = null;

    /**
     * @var Collection<int, RecipeVersion>
     */
    #[ORM\OneToMany(mappedBy: 'recipe', targetEntity: RecipeVersion::class, cascade: ['persist', 'remove'])]
    private Collection $versions;

    public function __construct(Uuid $id, User $user, string $title, int $servings)
    {
        $this->id = $id;
        $this->user = $user;
        $this->title = $title;
        $this->servings = $servings;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->versions = new ArrayCollection();
        $this->searchText = '';
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getActiveVersion(): ?RecipeVersion
    {
        return $this->activeVersion;
    }

    public function setActiveVersion(?RecipeVersion $activeVersion): void
    {
        $this->activeVersion = $activeVersion;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isFavorite(): bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(bool $isFavorite): void
    {
        $this->isFavorite = $isFavorite;
    }

    public function getSearchText(): string
    {
        return $this->searchText;
    }

    public function setSearchText(string $searchText): void
    {
        $this->searchText = $searchText;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getServings(): int
    {
        return $this->servings;
    }

    public function setServings(int $servings): void
    {
        $this->servings = $servings;
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

    /**
     * @return Collection<int, RecipeVersion>
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }
}
