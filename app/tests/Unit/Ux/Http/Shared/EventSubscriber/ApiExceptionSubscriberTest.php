<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ux\Http\Shared\EventSubscriber;

use App\Application\Exception\ParseGateException;
use App\Application\Exception\QuotaExceededException;
use App\Application\Exception\RecipeNotFoundException;
use App\Ux\Http\Shared\EventSubscriber\ApiExceptionSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionSubscriberTest extends TestCase
{
    private ApiExceptionSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new ApiExceptionSubscriber();
    }

    public function testGetSubscribedEvents(): void
    {
        // GIVEN
        $expected = [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];

        // WHEN
        $events = ApiExceptionSubscriber::getSubscribedEvents();

        // THEN
        $this->assertSame($expected, $events);
    }

    public function testHandleHttpException(): void
    {
        // GIVEN
        $exception = new BadRequestHttpException('Bad request message');
        $event = $this->createExceptionEvent($exception, '/api/test');

        // WHEN
        $this->subscriber->onKernelException($event);

        // THEN
        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJson($response->getContent(), ['error' => 'Bad request message']);
    }

    public function testHandleParseGateException(): void
    {
        // GIVEN
        $exception = new ParseGateException('Parse error', ['missing' => 'ingredients']);
        $event = $this->createExceptionEvent($exception, '/api/test');

        // WHEN
        $this->subscriber->onKernelException($event);

        // THEN
        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonStringEqualsJson($response->getContent(), [
            'error' => 'Parse error',
            'details' => ['missing' => 'ingredients'],
        ]);
    }

    public function testHandleQuotaExceededException(): void
    {
        // GIVEN
        $exception = new QuotaExceededException('Quota exceeded message');
        $event = $this->createExceptionEvent($exception, '/api/test');

        // WHEN
        $this->subscriber->onKernelException($event);

        // THEN
        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(429, $response->getStatusCode());
        $this->assertJsonStringEqualsJson($response->getContent(), ['error' => 'Quota exceeded message']);
    }

    public function testHandleRecipeNotFoundException(): void
    {
        // GIVEN
        $exception = new RecipeNotFoundException('Recipe not found');
        $event = $this->createExceptionEvent($exception, '/api/test');

        // WHEN
        $this->subscriber->onKernelException($event);

        // THEN
        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJson($response->getContent(), ['error' => 'Recipe not found']);
    }

    public function testHandleInvalidArgumentException(): void
    {
        // GIVEN
        $exception = new \InvalidArgumentException('Invalid argument');
        $event = $this->createExceptionEvent($exception, '/api/test');

        // WHEN
        $this->subscriber->onKernelException($event);

        // THEN
        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJson($response->getContent(), ['error' => 'Invalid argument']);
    }

    public function testHandleRuntimeException(): void
    {
        // GIVEN
        $exception = new \RuntimeException('Runtime error');
        $event = $this->createExceptionEvent($exception, '/api/test');

        // WHEN
        $this->subscriber->onKernelException($event);

        // THEN
        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJson($response->getContent(), ['error' => 'Runtime error']);
    }

    public function testDoesNotHandleOtherExceptions(): void
    {
        // GIVEN
        $exception = new \Exception('General error');
        $event = $this->createExceptionEvent($exception, '/api/test');

        // WHEN
        $this->subscriber->onKernelException($event);

        // THEN
        $this->assertNull($event->getResponse());
    }

    public function testDoesNotHandleExceptionsForNonApiPaths(): void
    {
        // GIVEN
        $exception = new RecipeNotFoundException('Recipe not found');
        $event = $this->createExceptionEvent($exception, '/some-other-path');

        // WHEN
        $this->subscriber->onKernelException($event);

        // THEN
        $this->assertNull($event->getResponse());
    }

    private function createExceptionEvent(\Throwable $exception, string $path = '/api/test'): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create($path),
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );
    }

    /**
     * @param array<string, mixed> $expected
     */
    private function assertJsonStringEqualsJson(string|bool|null $json, array $expected): void
    {
        $this->assertIsString($json);
        $this->assertJson($json);
        $this->assertEquals($expected, json_decode($json, true));
    }
}
