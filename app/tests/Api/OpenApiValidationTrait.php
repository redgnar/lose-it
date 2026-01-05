<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait OpenApiValidationTrait
{
    /** @var array<mixed>|null */
    private static ?array $openApiSchema = null;

    protected function assertResponseMatchesOpenApi(KernelBrowser $client, string $path, string $method, int $statusCode = 200): void
    {
        $response = $client->getResponse();
        \PHPUnit\Framework\Assert::assertSame($statusCode, $response->getStatusCode());

        if (null === self::$openApiSchema) {
            /** @var \Nelmio\ApiDocBundle\ApiDocGenerator $generator */
            $generator = $client->getContainer()->get('nelmio_api_doc.generator.default');
            $schemaJson = $generator->generate()->toJson();
            $decoded = json_decode($schemaJson, true);
            \PHPUnit\Framework\Assert::assertIsArray($decoded);
            self::$openApiSchema = $decoded;
        }

        /** @var array<string, mixed> $schema */
        $schema = self::$openApiSchema;
        \PHPUnit\Framework\Assert::assertArrayHasKey('paths', $schema);
        /** @var array<string, mixed> $paths */
        $paths = $schema['paths'];
        \PHPUnit\Framework\Assert::assertArrayHasKey($path, $paths);

        $method = strtolower($method);
        /** @var array<string, mixed> $pathData */
        $pathData = $paths[$path];
        \PHPUnit\Framework\Assert::assertArrayHasKey($method, $pathData);

        /** @var array<string, mixed> $methodData */
        $methodData = $pathData[$method];
        \PHPUnit\Framework\Assert::assertArrayHasKey('responses', $methodData);
        /** @var array<string, mixed> $responses */
        $responses = $methodData['responses'];
        \PHPUnit\Framework\Assert::assertArrayHasKey((string) $statusCode, $responses);

        $responseContent = $response->getContent();
        \PHPUnit\Framework\Assert::assertIsString($responseContent);
        $data = json_decode($responseContent, true);

        // If the response is 200 and has a schema, we do basic structural validation
        /** @var array<string, mixed> $statusResponse */
        $statusResponse = $responses[(string) $statusCode];
        if (200 === $statusCode && isset($statusResponse['content']) && is_array($statusResponse['content']) && isset($statusResponse['content']['application/json']) && is_array($statusResponse['content']['application/json']) && isset($statusResponse['content']['application/json']['schema']) && is_array($statusResponse['content']['application/json']['schema'])) {
            /** @var array<string, mixed> $responseSchema */
            $responseSchema = $statusResponse['content']['application/json']['schema'];
            $this->validateDataAgainstSchema($data, $responseSchema, $schema);
        }
    }

    /**
     * @param array<mixed> $propertySchema
     * @param array<mixed> $fullSchema
     */
    private function validateDataAgainstSchema(mixed $data, array $propertySchema, array $fullSchema): void
    {
        if (isset($propertySchema['$ref'])) {
            $ref = $propertySchema['$ref'];
            \PHPUnit\Framework\Assert::assertIsString($ref);
            $refPath = explode('/', $ref);
            $refSchema = $fullSchema;
            foreach ($refPath as $part) {
                if ('#' === $part) {
                    continue;
                }
                \PHPUnit\Framework\Assert::assertIsArray($refSchema);
                \PHPUnit\Framework\Assert::assertArrayHasKey($part, $refSchema);
                $refSchema = $refSchema[$part];
            }
            \PHPUnit\Framework\Assert::assertIsArray($refSchema);
            /* @var array<string, mixed> $refSchema */
            $this->validateDataAgainstSchema($data, $refSchema, $fullSchema);

            return;
        }

        if (isset($propertySchema['type'])) {
            switch ($propertySchema['type']) {
                case 'object':
                    \PHPUnit\Framework\Assert::assertIsArray($data);
                    /* @var array<string, mixed> $data */
                    if (isset($propertySchema['properties']) && is_array($propertySchema['properties'])) {
                        /** @var array<string, mixed> $properties */
                        $properties = $propertySchema['properties'];
                        foreach ($properties as $key => $subSchema) {
                            \PHPUnit\Framework\Assert::assertIsArray($subSchema);
                            /* @var array<string, mixed> $subSchema */
                            if (isset($propertySchema['required']) && is_array($propertySchema['required']) && in_array($key, $propertySchema['required'])) {
                                \PHPUnit\Framework\Assert::assertArrayHasKey($key, $data);
                            }
                            if (array_key_exists($key, $data)) {
                                if (null === $data[$key] && ($subSchema['nullable'] ?? false)) {
                                    continue;
                                }
                                $this->validateDataAgainstSchema($data[$key], $subSchema, $fullSchema);
                            }
                        }
                    }
                    break;
                case 'array':
                    \PHPUnit\Framework\Assert::assertIsArray($data);
                    /* @var array<mixed> $data */
                    if (isset($propertySchema['items']) && is_array($propertySchema['items'])) {
                        /** @var array<string, mixed> $itemsSchema */
                        $itemsSchema = $propertySchema['items'];
                        foreach ($data as $item) {
                            $this->validateDataAgainstSchema($item, $itemsSchema, $fullSchema);
                        }
                    }
                    break;
                case 'string':
                    \PHPUnit\Framework\Assert::assertIsString($data);
                    break;
                case 'integer':
                    \PHPUnit\Framework\Assert::assertIsInt($data);
                    break;
                case 'boolean':
                    \PHPUnit\Framework\Assert::assertIsBool($data);
                    break;
            }
        }
    }
}
