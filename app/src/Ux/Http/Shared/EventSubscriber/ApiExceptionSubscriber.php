<?php

declare(strict_types=1);

namespace App\Ux\Http\Shared\EventSubscriber;

use App\Application\Exception\ParseGateException;
use App\Application\Exception\QuotaExceededException;
use App\Application\Exception\RecipeNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            $event->setResponse(new JsonResponse([
                'error' => $exception->getMessage(),
            ], $exception->getStatusCode()));

            return;
        }

        if ($exception instanceof ParseGateException) {
            $event->setResponse(new JsonResponse([
                'error' => $exception->getMessage(),
                'details' => $exception->getDetails(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY));

            return;
        }

        if ($exception instanceof QuotaExceededException) {
            $event->setResponse(new JsonResponse([
                'error' => $exception->getMessage(),
            ], Response::HTTP_TOO_MANY_REQUESTS));

            return;
        }

        if ($exception instanceof RecipeNotFoundException) {
            $event->setResponse(new JsonResponse([
                'error' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND));

            return;
        }

        if ($exception instanceof \InvalidArgumentException || $exception instanceof \RuntimeException) {
            $event->setResponse(new JsonResponse([
                'error' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST));

            return;
        }
    }
}
