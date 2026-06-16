<?php

namespace App\Controller\Trait;

use App\Exception\Livro\LivroInvalidoException;
use App\Exception\Livro\LivroNaoEncontradoException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Centraliza o tratamento de exceções de domínio para controllers de API,
 * evitando duplicação de blocos try/catch e garantindo logging padronizado.
 *
 * Regras de log:
 * - LivroNaoEncontradoException  → logger->warning  (erro esperado, sem stack trace)
 * - LivroInvalidoException       → logger->warning  (violação de negócio, sem stack trace)
 * - \Throwable inesperado        → logger->error    (com stack trace completo)
 */
trait ApiExceptionHandlerTrait
{
    /**
     * Trata LivroNaoEncontradoException → HTTP 404.
     *
     * @param LivroNaoEncontradoException $e
     * @param LoggerInterface             $logger
     *
     * @return JsonResponse
     */
    protected function handleNaoEncontrado(
        LivroNaoEncontradoException $e,
        LoggerInterface $logger,
    ): JsonResponse {
        $logger->warning('Livro não encontrado.', [
            'mensagem' => $e->getMessage(),
        ]);

        return new JsonResponse(
            ['message' => $e->getMessage()],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Trata LivroInvalidoException → HTTP 400.
     *
     * Extrai todas as violações agregadas e as expõe no corpo da resposta.
     *
     * @param LivroInvalidoException $e
     * @param LoggerInterface        $logger
     *
     * @return JsonResponse
     */
    protected function handleInvalido(
        LivroInvalidoException $e,
        LoggerInterface $logger,
    ): JsonResponse {
        $violacoes = array_map(
            static fn (\Throwable $v): string => $v->getMessage(),
            $e->getViolacoes()  // ajuste o getter conforme sua implementação
        );

        $logger->warning('Requisição com dados inválidos.', [
            'violacoes' => $violacoes,
        ]);

        return new JsonResponse(
            [
                'message' => 'Dados inválidos.',
                'errors'  => $violacoes,
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Trata qualquer exceção inesperada → HTTP 500.
     *
     * Registra o stack trace completo para diagnóstico,
     * mas expõe apenas uma mensagem genérica ao cliente.
     *
     * @param \Throwable      $e
     * @param LoggerInterface $logger
     * @param string          $contexto  Descrição da operação que falhou.
     *
     * @return JsonResponse
     */
    protected function handleErroInesperado(
        \Throwable $e,
        LoggerInterface $logger,
        string $contexto = 'Operação',
    ): JsonResponse {
        $logger->error(sprintf('%s falhou com erro inesperado.', $contexto), [
            'exception' => $e::class,
            'mensagem'  => $e->getMessage(),
            'arquivo'   => $e->getFile(),
            'linha'     => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
        ]);

        return new JsonResponse(
            ['message' => 'Erro interno do servidor. Tente novamente mais tarde.'],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}