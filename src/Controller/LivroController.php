<?php

namespace App\Controller;

use App\Entity\Livro;
use App\Form\LivroType;
use App\Service\LivroService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Psr\Log\LoggerInterface;
use App\Exception\Livro\LivroInvalidoException;
use App\Exception\Livro\LivroNaoEncontradoException;

/**
 * Controller responsável pelo gerenciamento de livros.
 *
 * Disponibiliza operações de:
 * - Listagem paginada
 * - Visualização
 * - Cadastro
 * - Edição
 * - Remoção
 *
 * Toda regra de negócio é delegada ao LivroService.
 */

#[Route('/livros')]
class LivroController extends AbstractController
{
    /**
     * @param LivroService $livroService Serviço responsável pelas regras de negócio dos livros.
     */
    public function __construct(
        private readonly LivroService $livroService,
        private readonly LoggerInterface $logger
    ) {}

    /** @var list<int> */
    private const LIMITES_PERMITIDOS = [5, 10, 20, 50, 100];

    /**
     * Lista os livros de forma paginada.
     *
     * Permite configurar:
     * - página atual
     * - quantidade de registros por página
     *
     * @param Request $request Requisição HTTP contendo filtros de paginação.
     *
     * @return Response Página de listagem.
     */
    #[Route('/', name: 'livro_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limite = $request->query->getInt('limite', LivroService::LIVROS_POR_PAGINA);

        if (!in_array($limite, self::LIMITES_PERMITIDOS, strict: true)) {
            $limite = LivroService::LIVROS_POR_PAGINA;
        }

        $pagina = max(1, $request->query->getInt('pagina', 1));

        $resultado = $this->livroService->listarTodos($pagina, $limite);

        return $this->render('livro/index.html.twig', [
            'livros'            => $resultado->data,
            'totalPaginas'      => $resultado->pages,
            'paginaAtual'       => $pagina,
            'limiteAtual'       => $limite,
            'limitesPermitidos' => self::LIMITES_PERMITIDOS,
        ]);
    }

    /**
     * Exibe o formulário de criação de livro e processa seu envio.
     *
     * @param Request $request Requisição HTTP.
     *
     * @return Response Formulário ou redirecionamento após sucesso.
     */
    #[Route('/novo', name: 'livro_novo', methods: ['GET', 'POST'])]
    public function novo(Request $request): Response {
        $livro = new Livro();

        $form = $this->createForm(LivroType::class, $livro);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $this->livroService->criar($livro);

                $this->addFlash(
                    'success',
                    'Livro cadastrado com sucesso!'
                );

                return $this->redirectToRoute('livro_index');

            } catch (LivroInvalidoException $e) {

                foreach ($e->getViolacoes() as $violacao) {
                    $this->logger->error($violacao->getMessage(), ['exception' => $violacao]);
                }
            } catch (\Throwable $e) {
                $this->logger->error('Erro inesperado ao criar livro', ['exception' => $e]);
                $this->addFlash('error', 'Erro inesperado. Tente novamente mais tarde.');
            }
        }

        return $this->render('livro/novo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Exibe os detalhes de um livro.
     *
     * @param int $codl Código identificador do livro.
     *
     * @return Response Página de detalhes.
     * 
     * @throws LivroNaoEncontradoException Se nenhum livro for encontrado com o código informado.
     */
    #[Route('/{codl}', name: 'livro_show', methods: ['GET'], requirements: ['codl' => '\d+'])]
    public function show(int $codl): Response {
        try {
            $livro = $this->livroService->buscarPorCodigo($codl);
        } catch (LivroNaoEncontradoException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        return $this->render('livro/show.html.twig', [
            'livro' => $livro,
        ]);
    }

    /**
     * Exibe e processa o formulário de edição de um livro.
     *
     * @param Livro $livro Entidade carregada automaticamente pelo Symfony.
     * @param Request $request Requisição HTTP.
     *
     * @return Response Formulário ou redirecionamento após atualização.
     */
    #[Route('/{codl}/editar', name: 'livro_editar', methods: ['GET', 'POST'], requirements: ['codl' => '\d+'])]
    public function editar(
        #[MapEntity(id: 'codl')] Livro $livro,
        Request $request,
    ): Response {
        $form = $this->createForm(LivroType::class, $livro);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $this->livroService->atualizar($livro);

                $this->addFlash(
                    'success',
                    'Livro atualizado com sucesso!'
                );

                return $this->redirectToRoute('livro_index');

            } catch (LivroInvalidoException $e) {

                foreach ($e->getViolacoes() as $violacao) {
                    $this->logger->error($violacao->getMessage(), ['exception' => $violacao]);
                }

            } catch (\Throwable $e) {
                $this->logger->error('Erro inesperado ao criar livro', ['exception' => $e]);
                $this->addFlash('error', 'Erro inesperado. Tente novamente mais tarde.');
            }
        }

        return $this->render('livro/editar.html.twig', [
            'form' => $form->createView(),
            'livro' => $livro,
        ]);
    }

    /**
     * Remove um livro existente.
     *
     * @param Livro $livro Entidade carregada automaticamente pelo Symfony.
     * @param Request $request Requisição HTTP.
     * 
     * @return Response Redirecionamento para a listagem.
     */
    #[Route('/{codl}/remover', name: 'livro_remover', methods: ['POST'], requirements: ['codl' => '\d+'])]
    public function remover(
        #[MapEntity(id: 'codl')] Livro $livro,
        Request $request,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete' . $livro->getCodl(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException();
        }
        $this->livroService->remover($livro);

        $this->addFlash('success', 'Livro removido com sucesso.');

        return $this->redirectToRoute('livro_index');
    }

}