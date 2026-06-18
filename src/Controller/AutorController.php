<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Form\AutorType;
use App\Service\AutorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Psr\Log\LoggerInterface;
use App\Exception\Autor\AutorInvalidoException;
use App\Exception\Autor\AutorNaoEncontradoException;

/**
 * Controller responsável pelo gerenciamento de autores.
 *
 * Disponibiliza operações de:
 * - Listagem paginada
 * - Visualização
 * - Cadastro
 * - Edição
 * - Remoção
 *
 * Toda regra de negócio é delegada ao AutorService.
 */

#[Route('/autor')]
class AutorController extends AbstractController
{
    public function __construct(
        private readonly AutorService $autorService,
        private readonly LoggerInterface $logger
    ) {}

    /** @var list<int> */
    private const LIMITES_PERMITIDOS = [5, 10, 20, 50, 100];

    /**
     * Lista os autores de forma paginada.
     *
     * Permite configurar:
     * - página atual
     * - quantidade de registros por página
     *
     * @param Request $request Requisição HTTP contendo filtros de paginação.
     *
     * @return Response Página de listagem.
     */
    #[Route('/', name: 'autor_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limite = $request->query->getInt('limite', AutorService::AUTORES_POR_PAGINA);

        if (!in_array($limite, self::LIMITES_PERMITIDOS, strict: true)) {
            $limite = AutorService::AUTORES_POR_PAGINA;
        }

        $pagina = max(1, $request->query->getInt('pagina', 1));

        $resultado = $this->autorService->listarTodos($pagina, $limite);

        return $this->render('autor/index.html.twig', [
            'autores'          => $resultado->data,
            'totalPaginas'      => $resultado->pages,
            'paginaAtual'       => $pagina,
            'limiteAtual'       => $limite,
            'limitesPermitidos' => self::LIMITES_PERMITIDOS,
        ]);
    }

    /**
     * Exibe o formulário de criação de autores e processa seu envio.
     *
     * @param Request $request Requisição HTTP.
     *
     * @return Response Formulário ou redirecionamento após sucesso.
     */
    #[Route('/novo', name: 'autor_novo', methods: ['GET', 'POST'])]
    public function novo(Request $request): Response {
        $autor = new Autor();

        $form = $this->createForm(AutorType::class, $autor);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->autorService->criar($autor);

                $this->addFlash(
                    'success',
                    'Autor cadastrado com sucesso!'
                );

                return $this->redirectToRoute('autor_index');
            } catch (AutorInvalidoException $e) {
                foreach ($e->getViolacoes() as $violacao) {
                    $this->logger->error($violacao->getMessage(), ['exception' => $violacao]);
                }
            } catch (\Throwable $e) {
                $this->logger->error('Erro inesperado ao criar autor', ['exception' => $e]);
                $this->addFlash('error', 'Erro inesperado. Tente novamente mais tarde.');
            }
        }

        return $this->render('autor/novo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Exibe os detalhes de um Autor.
     *
     * @param int $codau Código identificador do autor.
     *
     * @return Response Página de detalhes.
     */
    #[Route('/{codau}', name: 'autor_show', methods: ['GET'], requirements: ['codau' => '\d+'])]
    public function show(int $codau): Response {
        try {    
            $autor = $this->autorService->buscarPorCodigo($codau);

        } catch (AutorNaoEncontradoException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        return $this->render('autor/show.html.twig', [
            'autor' => $autor,
        ]);
    }

    /**
     * Exibe e processa o formulário de edição de um autor.
     *
     * @param Autor $autor Entidade carregada automaticamente pelo Symfony.
     * @param Request $request Requisição HTTP.
     *
     * @return Response Formulário ou redirecionamento após atualização.
     */
    #[Route('/{codau}/editar', name: 'autor_editar', methods: ['GET', 'POST'], requirements: ['codau' => '\d+'])]
    public function editar(
        #[MapEntity(id: 'codau')] Autor $autor,
        Request $request,
    ): Response {
        $form = $this->createForm(AutorType::class, $autor);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->autorService->atualizar($autor);

                $this->addFlash(
                    'success',
                    'Autor atualizado com sucesso!'
                );

                return $this->redirectToRoute('autor_index');
            } catch (AutorInvalidoException $e) {

                foreach ($e->getViolacoes() as $violacao) {
                    $this->logger->error($violacao->getMessage(), ['exception' => $violacao]);
                }

            } catch (\Throwable $e) {
                $this->logger->error('Erro inesperado ao criar autor', ['exception' => $e]);
                $this->addFlash('error', 'Erro inesperado. Tente novamente mais tarde.');
            }
        }

        return $this->render('autor/editar.html.twig', [
            'form' => $form->createView(),
            'autor' => $autor,
        ]);
    }

    /**
     * Remove um autor existente.
     *
     * @param Autor $autor Entidade carregada automaticamente pelo Symfony.
     * @param Request $request Requisição HTTP.
     * 
     * @return Response Redirecionamento para a listagem.
     */
    #[Route('/{codau}/remover', name: 'autor_remover', methods: ['POST'], requirements: ['codau' => '\d+'])]
    public function remover(
        #[MapEntity(id: 'codau')] Autor $autor,
        Request $request,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete' . $autor->getCodau(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException();
        }
        $this->autorService->remover($autor);

        $this->addFlash('success', 'Autor removido com sucesso.');

        return $this->redirectToRoute('autor_index');
    }
}