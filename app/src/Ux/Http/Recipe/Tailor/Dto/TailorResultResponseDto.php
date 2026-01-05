<?php

declare(strict_types=1);

namespace App\Ux\Http\Recipe\Tailor\Dto;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

final readonly class TailorResultResponseDto
{
    public function __construct(
        #[OA\Property(ref: new Model(type: RecipeVersionResponseDto::class))]
        public RecipeVersionResponseDto $scaledVersion,
        #[OA\Property(ref: new Model(type: RecipeVersionResponseDto::class))]
        public RecipeVersionResponseDto $finalVersion,
        #[OA\Property(description: 'Status of the tailoring process', type: 'string', example: 'success')]
        public string $status,
        #[OA\Property(description: 'Optional message from the tailoring process', type: 'string', example: 'Recipe tailored successfully', nullable: true)]
        public ?string $message = null,
    ) {
    }

    public static function fromApplicationDto(\App\Application\Dto\TailorResultDto $dto): self
    {
        return new self(
            RecipeVersionResponseDto::fromApplicationDto($dto->scaledVersion),
            RecipeVersionResponseDto::fromApplicationDto($dto->finalVersion),
            $dto->status,
            $dto->message
        );
    }
}
