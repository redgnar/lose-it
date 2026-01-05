<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\Index(columns: ['email'], name: 'idx_users_email')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    #[ORM\Column(length: 255, unique: true)]
    private string $googleId;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserPreference::class, cascade: ['persist', 'remove'])]
    private ?UserPreference $preferences = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: TailoringQuota::class, cascade: ['persist', 'remove'])]
    private ?TailoringQuota $quota = null;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Recipe::class, cascade: ['persist', 'remove'])]
    private Collection $recipes;

    /**
     * @var Collection<int, IngredientRegistry>
     */
    #[ORM\ManyToMany(targetEntity: IngredientRegistry::class)]
    #[ORM\JoinTable(name: 'user_avoid_list')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'ingredient_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $avoidList;

    public function __construct(Uuid $id, string $email, string $googleId)
    {
        $this->id = $id;
        $this->email = $email;
        $this->googleId = $googleId;
        $this->createdAt = new \DateTimeImmutable();
        $this->recipes = new ArrayCollection();
        $this->avoidList = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getGoogleId(): string
    {
        return $this->googleId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function getPreferences(): ?UserPreference
    {
        return $this->preferences;
    }

    public function setPreferences(UserPreference $preferences): void
    {
        $this->preferences = $preferences;
    }

    public function getQuota(): ?TailoringQuota
    {
        return $this->quota;
    }

    public function setQuota(TailoringQuota $quota): void
    {
        $this->quota = $quota;
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    /**
     * @return Collection<int, IngredientRegistry>
     */
    public function getAvoidList(): Collection
    {
        return $this->avoidList;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        /** @var non-empty-string $email */
        $email = $this->email;

        return $email;
    }
}
