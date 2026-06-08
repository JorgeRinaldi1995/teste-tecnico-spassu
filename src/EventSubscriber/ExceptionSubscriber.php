<?php

namespace App\EventSubscriber;

use App\Exception\Livro\AnoPublicacaoInvalidoException;
use App\Exception\Livro\LivroNaoEncontradoException;
use App\Exception\Livro\LivroSemAssuntoException;
use App\Exception\Livro\LivroSemAutorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = match (true) {
            $exception instanceof LivroNaoEncontradoException => 404,

            $exception instanceof AnoPublicacaoInvalidoException,
            $exception instanceof LivroSemAutorException,
            $exception instanceof LivroSemAssuntoException => 422,

            default => 500,
        };

        $response = new Response(
            $this->twig->render('error/error.html.twig', [
                'message' => $exception->getMessage(),
                'status_code' => $statusCode,
            ]),
            $statusCode
        );

        $event->setResponse($response);
    }
}