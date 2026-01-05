<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OpenApiTest extends WebTestCase
{
    public function testOpenApiJsonIsAccessibleAndValid(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/doc.json');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);

        $this->assertArrayHasKey('openapi', $data);
        $this->assertArrayHasKey('info', $data);
        $this->assertArrayHasKey('paths', $data);

        $paths = $data['paths'];
        $this->assertIsArray($paths);
        $this->assertArrayHasKey('/api/recipes/{id}/tailor', $paths);
    }
}
