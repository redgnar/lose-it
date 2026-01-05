<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

/**
 * @implements \IteratorAggregate<int, mixed>
 */
final readonly class RecipeStepCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array<mixed>
     */
    private array $steps;

    public function __construct(
        mixed ...$steps,
    ) {
        $this->steps = $steps;
    }

    /**
     * @return \Traversable<int, mixed>
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->steps as $step) {
            yield $step;
        }
    }

    public function count(): int
    {
        return count($this->steps);
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->steps;
    }
}
