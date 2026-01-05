<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Tailor\Dto;

use OpenApi\Attributes as OA;

final readonly class RecipeIngredientResponseDto
{
    public function __construct(
        #[OA\Property(description: 'ID of the ingredient', type: 'string', format: 'uuid', example: '018f3b4a-1234-7890-abcd-1234567890ab', nullable: true)]
        public ?string $id,
        #[OA\Property(description: 'Original text of the ingredient', type: 'string', example: '200g of chicken breast')]
        public string $originalText,
        #[OA\Property(description: 'Parsed quantity', type: 'string', example: '200', nullable: true)]
        public ?string $quantity,
        #[OA\Property(description: 'Parsed unit', type: 'string', example: 'g', nullable: true)]
        public ?string $unit,
        #[OA\Property(description: 'Parsed item name', type: 'string', example: 'chicken breast', nullable: true)]
        public ?string $item,
        #[OA\Property(description: 'Whether the ingredient was successfully parsed', type: 'boolean', example: true)]
        public bool $isParsed,
    ) {
    }

    public static function fromApplicationDto(\App\Application\Dto\RecipeIngredientDto $dto): self
    {
        return new self(
            $dto->id,
            $dto->originalText,
            $dto->quantity,
            $dto->unit,
            $dto->item,
            $dto->isParsed
        );
    }
}
