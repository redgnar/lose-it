<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Contract\MeasurementKeywordsProviderInterface;
use App\Domain\Contract\ScalingServiceInterface;
use App\Domain\Enum\Servings;
use App\Domain\Model\RecipeIngredient;
use App\Domain\Model\RecipeVersion;
use App\Domain\ValueObject\MeasurementKeywords;
use App\Domain\ValueObject\RecipeIngredientCollection;
use App\Domain\ValueObject\RecipeStepCollection;
use Symfony\Component\Uid\Uuid;

final class ScalingService implements ScalingServiceInterface
{
    public function __construct(private MeasurementKeywordsProviderInterface $keywordsProvider)
    {
    }

    public function scale(RecipeVersion $sourceVersion, Servings $targetServings): RecipeVersion
    {
        $ratio = $targetServings->value / $sourceVersion->servings->value;
        $keywords = $this->keywordsProvider->getKeywords();

        $scaledIngredients = [];
        foreach ($sourceVersion->ingredients as $sourceIngredient) {
            $newQuantity = $sourceIngredient->quantity;
            $newOriginalText = $sourceIngredient->originalText;

            if ($sourceIngredient->isScalable && null !== $sourceIngredient->quantity) {
                $scaledQuantity = (float) $sourceIngredient->quantity * $ratio;
                $newQuantity = (string) $scaledQuantity;

                if ($sourceIngredient->isParsed) {
                    $newOriginalText = $this->updateQuantityInText(
                        $sourceIngredient->originalText,
                        (float) $sourceIngredient->quantity,
                        $scaledQuantity
                    );
                }
            }

            $scaledIngredients[] = new RecipeIngredient(
                $newOriginalText,
                $newQuantity,
                $sourceIngredient->isParsed,
                $sourceIngredient->isScalable,
                $sourceIngredient->ingredientId,
                $sourceIngredient->unitId
            );
        }

        $newTotalCalories = null;
        if (null !== $sourceVersion->totalCalories) {
            $newTotalCalories = (int) round($sourceVersion->totalCalories * $ratio);
        }

        return new RecipeVersion(
            Uuid::v7(),
            $sourceVersion->recipeId,
            $targetServings,
            new RecipeIngredientCollection(...$scaledIngredients),
            new RecipeStepCollection(...$this->scaleSteps($sourceVersion->steps, $ratio, $keywords)),
            $newTotalCalories,
            $sourceVersion->caloriesPerServing
        );
    }

    private function updateQuantityInText(string $text, float $oldQty, float $newQty): string
    {
        $oldQtyStr = (string) $oldQty;

        // Simple replacement for now, might need more robust regex
        return str_replace($oldQtyStr, (string) $newQty, $text);
    }

    /**
     * @return array<mixed>
     */
    private function scaleSteps(RecipeStepCollection $steps, float $ratio, MeasurementKeywords $keywords): array
    {
        $scaledSteps = [];
        foreach ($steps as $step) {
            if (is_string($step)) {
                $scaledSteps[] = $this->scaleText($step, $ratio, $keywords);
            } elseif (is_array($step) && isset($step['instruction']) && is_string($step['instruction'])) {
                $step['instruction'] = $this->scaleText($step['instruction'], $ratio, $keywords);
                $scaledSteps[] = $step;
            } else {
                $scaledSteps[] = $step;
            }
        }

        return $scaledSteps;
    }

    private function scaleText(string $text, float $ratio, MeasurementKeywords $keywords): string
    {
        // Regex to find numbers, and check for following unit or punctuation
        $result = preg_replace_callback('/(\d+(?:\.\d+)?)(?:\s*([a-zA-Z%]+))?/', function ($matches) use ($ratio, $text, $keywords) {
            $number = (float) $matches[1];
            $unit = isset($matches[2]) ? strtolower($matches[2]) : '';
            $fullMatch = $matches[0];

            if (in_array($unit, $keywords->excludedUnits, true)) {
                return $fullMatch;
            }

            // 2. Exclude Step numbering (e.g., "Step 1", "1.")
            // Look behind in the original text to see if it's preceded by any step prefix
            // Or look ahead to see if it's followed by ":" or "."
            $offset = strpos($text, $fullMatch);
            if (false !== $offset) {
                foreach ($keywords->stepPrefixes as $prefix) {
                    $prefixLen = strlen($prefix);
                    $actualPrefix = substr($text, max(0, $offset - ($prefixLen + 1)), $prefixLen + 1);
                    if (false !== stripos($actualPrefix, $prefix)) {
                        return $fullMatch;
                    }
                }

                $suffix = substr($text, $offset + strlen($fullMatch), 1);
                if ('.' === $suffix || ':' === $suffix) {
                    // Check if it's at the start of the line or preceded by space
                    $charBefore = $offset > 0 ? $text[$offset - 1] : "\n";
                    if ("\n" === $charBefore || ' ' === $charBefore) {
                        return $fullMatch;
                    }
                }
            }

            $scaled = $number * $ratio;
            $scaledStr = (string) round($scaled, 2);

            // Reconstruct with original spacing
            if (isset($matches[2])) {
                // If there was a unit, we should preserve whether there was a space or not
                // matches[0] contains the full match including possible spaces before unit
                return str_replace($matches[1], $scaledStr, $fullMatch);
            }

            return $scaledStr;
        }, $text);

        return $result ?? $text;
    }
}
