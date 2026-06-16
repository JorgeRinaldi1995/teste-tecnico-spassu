<?php

namespace App\Controller\Api;

use App\Controller\Trait\ApiExceptionHandlerTrait;
use App\Entity\Livro;
use App\Exception\Livro\LivroInvalidoException;
use App\Exception\Livro\LivroNaoEncontradoException;
use App\Service\LivroService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller de API REST para o recurso Livro.
 *
 * Contrato de respostas:
 * ┌─────────────────────────┬──────┬───────────────────────────────────────┐
 * │ Situação                │ HTTP │ Corpo                                 │
 * ├─────────────────────────┼──────┼───────────────────────────────────────┤
 * │ Listagem OK             │ 200  │ { data, total, pages }                │
 * │ Recurso encontrado      │ 200  │ { ...campos do livro }                │
 * │ Criado com sucesso      │ 201  │ { message, data }                     │
 * │ Atualizado com sucesso  │ 200  │ { message, data }                     │
 * │ Removido com sucesso    │ 200  │ { message }                           │
 * │ Dados inválidos         │ 400  │ { message, errors[] }                 │
 * │ Não encontrado          │ 404  │ { message }                           │
 * │ Erro inesperado         │ 500  │ { message }                           │
 * └─────────────────────────┴──────┴───────────────────────────────────────┘
 *
 * Logging:
 * - Erros de domínio e 404 → LoggerInterface::warning (sem stack trace)
 * - Erros inesperados       → LoggerInterface::error   (com stack trace)
 */
#[Route('/api/livros', name: 'api_livro_')]
class LivroController extends AbstractController
{
    use ApiExceptionHandlerTrait;

    public function __construct(
        private readonly LivroService         $livroService,
        private readonly SerializerInterface  $serializer,
        private readonly ValidatorInterface   $validator,
        private readonly LoggerInterface      $logger,
    ) {}

    // =========================================================================
    // GET /api/livros
    // =========================================================================

    /**
     * Lista livros de forma paginada.
     *
     * Query params:
     * - pagina (int, default 1)
     * - limite (int, default 20, max 100)
     *
     * @return JsonResponse
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $pagina = max(1, $request->query->getInt('pagina', 1));
        $limite = max(1, min(100, $request->query->getInt('limite', LivroService::LIVROS_POR_PAGINA)));

        try {
            $resultado = $this->livroService->listarTodos($pagina, $limite);
        } catch (\Throwable $e) {
            return $this->handleErroInesperado($e, $this->logger, 'Listagem de livros');
        }

        return new JsonResponse([
            'data'  => $resultado->data,
            'total' => $resultado->total,
            'pages' => $resultado->pages,
        ]);
    }

    // =========================================================================
    // GET /api/livros/{codl}
    // =========================================================================

    /**
     * Retorna um livro pelo código.
     *
     * @param int $codl Código (PK) do livro.
     *
     * @return JsonResponse
     */
    #[Route('/{codl}', name: 'show', methods: ['GET'], requirements: ['codl' => '\d+'])]
    public function show(int $codl): JsonResponse
    {
        try {
            $livro = $this->livroService->buscarPorCodigo($codl);
        } catch (LivroNaoEncontradoException $e) {
            return $this->handleNaoEncontrado($e, $this->logger);
        } catch (\Throwable $e) {
            return $this->handleErroInesperado($e, $this->logger, "Busca do livro #$codl");
        }

        return new JsonResponse($livro, Response::HTTP_OK);
    }

    // =========================================================================
    // POST /api/livros
    // =========================================================================

    /**
     * Cria um novo livro.
     *
     * Corpo esperado: JSON com os campos da entidade Livro.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('', name: 'criar', methods: ['POST'])]
    public function criar(Request $request): JsonResponse
    {
        try {
            /** @var Livro $livro */
            $livro = $this->serializer->deserialize(
                $request->getContent(),
                Livro::class,
                'json',
            );

            $this->livroService->criar($livro);

        } catch (LivroInvalidoException $e) {
            return $this->handleInvalido($e, $this->logger);
        } catch (\Throwable $e) {
            return $this->handleErroInesperado($e, $this->logger, 'Criação de livro');
        }

        return new JsonResponse(
            [
                'message' => 'Livro criado com sucesso.',
                'data'    => $livro,
            ],
            Response::HTTP_CREATED,
        );
    }

    // =========================================================================
    // PUT /api/livros/{codl}
    // =========================================================================

    /**
     * Atualiza um livro existente (substituição completa).
     *
     * @param int     $codl    Código (PK) do livro.
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/{codl}', name: 'atualizar', methods: ['PUT'], requirements: ['codl' => '\d+'])]
    public function atualizar(int $codl, Request $request): JsonResponse
    {
        try {
            $livro = $this->livroService->buscarPorCodigo($codl);

            $this->serializer->deserialize(
                $request->getContent(),
                Livro::class,
                'json',
                ['object_to_populate' => $livro],   // atualiza a entidade gerenciada
            );

            $this->livroService->atualizar($livro);

        } catch (LivroNaoEncontradoException $e) {
            return $this->handleNaoEncontrado($e, $this->logger);
        } catch (LivroInvalidoException $e) {
            return $this->handleInvalido($e, $this->logger);
        } catch (\Throwable $e) {
            return $this->handleErroInesperado($e, $this->logger, "Atualização do livro #$codl");
        }

        return new JsonResponse([
            'message' => 'Livro atualizado com sucesso.',
            'data'    => $livro,
        ]);
    }

    // =========================================================================
    // DELETE /api/livros/{codl}
    // =========================================================================

    /**
     * Remove um livro.
     *
     * @param int $codl Código (PK) do livro.
     *
     * @return JsonResponse
     */
    #[Route('/{codl}', name: 'remover', methods: ['DELETE'], requirements: ['codl' => '\d+'])]
    public function remover(int $codl): JsonResponse
    {
        try {
            $livro = $this->livroService->buscarPorCodigo($codl);
            $this->livroService->remover($livro);

        } catch (LivroNaoEncontradoException $e) {
            return $this->handleNaoEncontrado($e, $this->logger);
        } catch (\Throwable $e) {
            return $this->handleErroInesperado($e, $this->logger, "Remoção do livro #$codl");
        }

        return new JsonResponse(['message' => 'Livro removido com sucesso.']);
    }
}