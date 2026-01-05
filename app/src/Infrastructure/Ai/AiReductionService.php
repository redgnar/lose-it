<?php

declare(strict_types=1);

namespace App\Infrastructure\Ai;

use App\Application\Contract\AiReductionServiceInterface;
use App\Domain\Enum\Aggressiveness;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class AiReductionService implements AiReductionServiceInterface
{
    public function __construct(
        #[Autowire('%openrouter_api_key%')]
        private string $apiKey,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $scaledVersionData
     * @param string[]             $avoidList
     *
     * @return array<string, mixed>
     */
    public function reduce(
        array $scaledVersionData,
        Aggressiveness $aggressiveness,
        bool $keepSimilar,
        array $avoidList = [],
        ?int $targetCalories = null,
    ): array {
        if (empty($this->apiKey)) {
            $this->logger->warning('OpenRouter API key is not configured. Skipping AI reduction.');

            return $scaledVersionData;
        }

        $prompt = $this->buildPrompt($scaledVersionData, $aggressiveness, $keepSimilar, $avoidList, $targetCalories);

        try {
            $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
            if (!$ch) {
                return $scaledVersionData;
            }

            $body = json_encode([
                'model' => 'google/gemini-2.0-flash-001',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional chef and nutritionist specializing in low-calorie recipe adaptation.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
            ], JSON_THROW_ON_ERROR);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer '.$this->apiKey,
                'HTTP-Referer: https://lose-it.app',
                'X-Title: Lose It Recipe Tailor',
                'Content-Type: application/json',
            ]);

            $response = curl_exec($ch);
            if (is_bool($response)) {
                curl_close($ch);

                return $scaledVersionData;
            }

            $result = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            curl_close($ch);

            if (!is_array($result)) {
                return $scaledVersionData;
            }

            /** @var array<int, array{message: array{content: string}}> $choices */
            $choices = $result['choices'] ?? [];
            if (empty($choices)) {
                return $scaledVersionData;
            }

            $contentJson = $choices[0]['message']['content'];
            /** @var array{ingredients?: array<int, mixed>, steps?: array<int, string>} $content */
            $content = json_decode($contentJson, true, 512, JSON_THROW_ON_ERROR);

            return [
                'ingredients' => $content['ingredients'] ?? $scaledVersionData['ingredients'],
                'steps' => $content['steps'] ?? $scaledVersionData['steps'],
            ];
        } catch (\Throwable $e) {
            $this->logger->error('AI reduction failed: '.$e->getMessage());

            return $scaledVersionData;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param string[]             $avoidList
     */
    private function buildPrompt(
        array $data,
        Aggressiveness $aggressiveness,
        bool $keepSimilar,
        array $avoidList,
        ?int $targetCalories,
    ): string {
        $ingredientsStr = json_encode($data['ingredients'], JSON_PRETTY_PRINT);
        $stepsStr = json_encode($data['steps'], JSON_PRETTY_PRINT);
        $avoidStr = implode(', ', $avoidList);
        $targetStr = $targetCalories ? "Target calories per serving: {$targetCalories} kcal." : '';

        $keepSimilarInstruction = $keepSimilar
            ? 'Try to keep the recipe as similar to the original as possible. Focus on reducing calorie-dense ingredients (fats, sugars) without changing the core nature of the dish. Preserve core proteins.'
            : 'You have more freedom to swap ingredients to achieve lower calories, even if it changes the dish significantly.';

        return <<<PROMPT
Modify the following recipe to reduce its calorie content.
Aggressiveness level: {$aggressiveness->value} (low = minor tweaks, medium = some swaps, high = significant changes).
{$targetStr}
Avoid ingredients: {$avoidStr}
{$keepSimilarInstruction}

Original Ingredients:
{$ingredientsStr}

Original Steps:
{$stepsStr}

Return ONLY a JSON object with the following structure:
{
  "ingredients": [
    {
      "originalText": "updated text",
      "quantity": "updated quantity",
      "isParsed": true,
      "isScalable": true,
      "ingredientId": "original uuid or null",
      "unitId": "original uuid or null"
    }
  ],
  "steps": ["updated step 1", "updated step 2"]
}
Maintain the original ingredientId and unitId if the ingredient is similar. If you swap an ingredient completely, set ingredientId and unitId to null.
PROMPT;
    }
}
