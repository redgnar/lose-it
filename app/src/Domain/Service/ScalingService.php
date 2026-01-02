<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Contract\ScalingServiceInterface;
use App\Domain\Enum\Servings;
use App\Infrastructure\Doctrine\Entity\RecipeIngredient;
use App\Infrastructure\Doctrine\Entity\RecipeVersion;
use Symfony\Component\Uid\Uuid;

final class ScalingService implements ScalingServiceInterface
{
    public function scale(RecipeVersion $sourceVersion, Servings $targetServings): RecipeVersion
    {
        $ratio = $targetServings->value / $sourceVersion->getServings();

        $scaledVersion = new RecipeVersion(
            Uuid::v7(),
            $sourceVersion->getRecipe(),
            $targetServings->value,
            'scale'
        );
        $scaledVersion->setParentVersion($sourceVersion);

        foreach ($sourceVersion->getIngredients() as $sourceIngredient) {
            $scaledIngredient = new RecipeIngredient($scaledVersion, $sourceIngredient->getOriginalText());
            $scaledIngredient->setIngredient($sourceIngredient->getIngredient());
            $scaledIngredient->setUnit($sourceIngredient->getUnit());
            $scaledIngredient->setIsParsed($sourceIngredient->isParsed());
            $scaledIngredient->setIsScalable($sourceIngredient->isScalable());

            if ($sourceIngredient->isScalable() && null !== $sourceIngredient->getQuantity()) {
                $newQuantity = (float) $sourceIngredient->getQuantity() * $ratio;
                $scaledIngredient->setQuantity((string) $newQuantity);

                // Update original text if it was parsed
                if ($sourceIngredient->isParsed()) {
                    $scaledIngredient->setOriginalText($this->updateQuantityInText(
                        $sourceIngredient->getOriginalText(),
                        (float) $sourceIngredient->getQuantity(),
                        $newQuantity
                    ));
                }
            } else {
                $scaledIngredient->setQuantity($sourceIngredient->getQuantity());
            }

            $scaledVersion->getIngredients()->add($scaledIngredient);
        }

        $scaledVersion->setSteps($this->scaleSteps($sourceVersion->getSteps(), $ratio));

        // Calories are also scaled
        if (null !== $sourceVersion->getTotalCalories()) {
            $scaledVersion->setTotalCalories((int) round($sourceVersion->getTotalCalories() * $ratio));
        }
        $scaledVersion->setCaloriesPerServing($sourceVersion->getCaloriesPerServing());

        return $scaledVersion;
    }

    private function updateQuantityInText(string $text, float $oldQty, float $newQty): string
    {
        $oldQtyStr = (string) $oldQty;

        // Simple replacement for now, might need more robust regex
        return str_replace($oldQtyStr, (string) $newQty, $text);
    }

    /**
     * @param array<mixed> $steps
     *
     * @return array<mixed>
     */
    private function scaleSteps(array $steps, float $ratio): array
    {
        $scaledSteps = [];
        foreach ($steps as $step) {
            if (is_string($step)) {
                $scaledSteps[] = $this->scaleText($step, $ratio);
            } elseif (is_array($step) && isset($step['instruction']) && is_string($step['instruction'])) {
                $step['instruction'] = $this->scaleText($step['instruction'], $ratio);
                $scaledSteps[] = $step;
            } else {
                $scaledSteps[] = $step;
            }
        }

        return $scaledSteps;
    }

    private function scaleText(string $text, float $ratio): string
    {
        // Regex to find numbers. This is a simplified version.
        $result = preg_replace_callback('/(\d+(?:\.\d+)?)/', function ($matches) use ($ratio) {
            $number = (float) $matches[1];
            // We only scale numbers if they look like quantities (heuristic)
            // For now, let's scale all numbers found in steps as per plan "uses regex to update numeric values in the steps"
            $scaled = $number * $ratio;

            return (string) round($scaled, 2);
        }, $text);

        return $result ?? $text;
    }
}
