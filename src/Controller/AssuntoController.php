<?php

namespace App\Controller;

use App\Entity\Assunto;
use App\Form\AssuntoType;
use App\Service\AssuntoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Psr\Log\LoggerInterface;

/**
 * Controller responsável pelo gerenciamento de assuntos.
 *
 * Disponibiliza operações de:
 * - Listagem paginada
 * - Visualização
 * - Cadastro
 * - Edição
 * - Remoção
 *
 * Toda regra de negócio é delegada ao AssuntosService.
 */

#[Route('/assunto')]
class AssuntoController extends AbstractController
{
    /**
     * @param AssuntoService $assuntoService Serviço responsável pelas regras de negócio dos assuntos.
     */
    public function __construct(
        private readonly AssuntoService $assuntoService,
        private readonly LoggerInterface $logger
    ) {}

    /** @var list<int> */
    private const LIMITES_PERMITIDOS = [5, 10, 20, 50, 100];


    /**
     * Lista os assuntos de forma paginada.
     *
     * Permite configurar:
     * - página atual
     * - quantidade de registros por página
     *
     * @param Request $request Requisição HTTP contendo filtros de paginação.
     *
     * @return Response Página de listagem.
     */
    #[Route('/', name: 'assunto_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limite = $request->query->getInt('limite', AssuntoService::ASSUNTOS_POR_PAGINA);

        if (!in_array($limite, self::LIMITES_PERMITIDOS, strict: true)) {
            $limite = AssuntoService::ASSUNTOS_POR_PAGINA;
        }

        $pagina = max(1, $request->query->getInt('pagina', 1));

        $resultado = $this->assuntoService->listarAssuntos($pagina, $limite);

        return $this->render('assunto/index.html.twig', [
            'assuntos'          => $resultado->data,
            'totalPaginas'      => $resultado->pages,
            'paginaAtual'       => $pagina,
            'limiteAtual'       => $limite,
            'limitesPermitidos' => self::LIMITES_PERMITIDOS,
        ]);
    }

    /**
     * Exibe o formulário de criação de assunto e processa seu envio.
     *
     * @param Request $request Requisição HTTP.
     *
     * @return Response Formulário ou redirecionamento após sucesso.
     */
    #[Route('/novo', name: 'assunto_novo', methods: ['GET', 'POST'])]
    public function novo(Request $request): Response {
        $assunto = new Assunto();

        $form = $this->createForm(AssuntoType::class, $assunto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->assuntoService->criar($assunto);

                $this->addFlash(
                    'success',
                    'Assunto cadastrado com sucesso!'
                );

                return $this->redirectToRoute('assunto_index');

            } catch (AssuntoInvalidoException $e) {

                foreach ($e->getViolacoes() as $violacao) {
                    $this->logger->error($violacao->getMessage(), ['exception' => $violacao]);
                }
            } catch (\Throwable $e) {
                $this->logger->error('Erro inesperado ao criar assunto', ['exception' => $e]);
                $this->addFlash('error', 'Erro inesperado. Tente novamente mais tarde.');
            }
        }

        return $this->render('assunto/novo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Exibe os detalhes de um Assunto.
     *
     * @param int $codas Código identificador do assunto.
     *
     * @return Response Página de detalhes.
     */
    #[Route('/{codas}', name: 'assunto_show', methods: ['GET'], requirements: ['codas' => '\d+'])]
    public function show(int $codas): Response {
        $assunto = $this->assuntoService->buscarPorCodigo($codas);

        if (!$assunto) {
            throw $this->createNotFoundException(
                'Assunto não encontrado.'
            );
        }

        return $this->render('assunto/show.html.twig', [
            'assunto' => $assunto,
        ]);
    }

    /**
     * Exibe e processa o formulário de edição de um assunto.
     *
     * @param Assunto $assunto Entidade carregada automaticamente pelo Symfony.
     * @param Request $request Requisição HTTP.
     *
     * @return Response Formulário ou redirecionamento após atualização.
     */
    #[Route('/{codas}/editar', name: 'assunto_editar', methods: ['GET', 'POST'], requirements: ['codas' => '\d+'])]
    public function editar(
        #[MapEntity(id: 'codas')] Assunto $assunto,
        Request $request,
    ): Response {
        $form = $this->createForm(AssuntoType::class, $assunto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->assuntoService->atualizar($assunto);

                $this->addFlash(
                    'success',
                    'Assunto atualizado com sucesso!'
                );

                return $this->redirectToRoute('assunto_index');
            } catch (AssuntoInvalidoException $e) {

                foreach ($e->getViolacoes() as $violacao) {
                    $this->logger->error($violacao->getMessage(), ['exception' => $violacao]);
                }

            } catch (\Throwable $e) {
                $this->logger->error('Erro inesperado ao criar assunto', ['exception' => $e]);
                $this->addFlash('error', 'Erro inesperado. Tente novamente mais tarde.');
            }

        }

        return $this->render('assunto/editar.html.twig', [
            'form' => $form->createView(),
            'assunto' => $assunto,
        ]);
    }

    /**
     * Remove um assunto existente.
     *
     * @param Assunto $assunto Entidade carregada automaticamente pelo Symfony.
     * @param Request $request Requisição HTTP.
     * 
     * @return Response Redirecionamento para a listagem.
     */
    #[Route('/{codas}/remover', name: 'assunto_remover', methods: ['POST'], requirements: ['codas' => '\d+'])]
    public function remover(
        #[MapEntity(id: 'codas')] Assunto $assunto,
        Request $request,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete' . $assunto->getCodas(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException();
        }
        $this->assuntoService->remover($assunto);

        $this->addFlash('success', 'Assunto removido com sucesso.');

        return $this->redirectToRoute('assunto_index');
    }
}