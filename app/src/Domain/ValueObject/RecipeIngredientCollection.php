<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Model\RecipeIngredient;

/**
 * @implements \IteratorAggregate<int, RecipeIngredient>
 */
final readonly class RecipeIngredientCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var RecipeIngredient[]
     */
    private array $ingredients;

    public function __construct(
        RecipeIngredient ...$ingredients,
    ) {
        $this->ingredients = $ingredients;
    }

    /**
     * @return \Traversable<int, RecipeIngredient>
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->ingredients as $ingredient) {
            yield $ingredient;
        }
    }

    public function count(): int
    {
        return count($this->ingredients);
    }

    /**
     * @return RecipeIngredient[]
     */
    public function toArray(): array
    {
        return $this->ingredients;
    }
}
