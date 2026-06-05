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

#[Route('/livros')]
class LivroController extends AbstractController
{
    public function __construct(
        private readonly LivroService $livroService
    ) {}

    #[Route('/', name: 'livro_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limitesPermitidos = [5, 10, 20, 50, 100];
        $limite = $request->query->getInt('limite', LivroService::LIVROS_POR_PAGINA);

        if (!in_array($limite, $limitesPermitidos, true)) {
            $limite = LivroService::LIVROS_POR_PAGINA;
        }

        $pagina = max(1, $request->query->getInt('pagina', 1));

        $resultado = $this->livroService->listarTodos($pagina, $limite);

        return $this->render('livro/index.html.twig', [
            'livros'           => $resultado['data'],
            'totalPaginas'     => $resultado['pages'],
            'paginaAtual'      => $pagina,
            'limiteAtual'      => $limite,
            'limitesPermitidos' => $limitesPermitidos,
        ]);
    }

    #[Route('/novo', name: 'livro_novo', methods: ['GET', 'POST'])]
    public function novo(Request $request): Response {
        $livro = new Livro();

        $form = $this->createForm(LivroType::class, $livro);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->livroService->criar($livro);

            $this->addFlash('success', 'Livro cadastrado com sucesso!');
            return $this->redirectToRoute('livro_index');
        }

        return $this->render('livro/novo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{codl}', name: 'livro_show', methods: ['GET'], requirements: ['codl' => '\d+'])]
    public function show(int $codl): Response {
        $livro = $this->livroService->buscarPorCodigo($codl);

        if (!$livro) {
            throw $this->createNotFoundException(
                'Livro não encontrado.'
            );
        }

        return $this->render('livro/show.html.twig', [
            'livro' => $livro,
        ]);
    }

    #[Route('/{codl}/editar', name: 'livro_editar', methods: ['GET', 'POST'], requirements: ['codl' => '\d+'])]
    public function editar(
        #[MapEntity(id: 'codl')] Livro $livro,
        Request $request,
    ): Response {
        $form = $this->createForm(LivroType::class, $livro);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->livroService->atualizar($livro);

            return $this->redirectToRoute('livro_index');
        }

        return $this->render('livro/editar.html.twig', [
            'form' => $form->createView(),
            'livro' => $livro,
        ]);
    }

    #[Route('/{codl}/remover', name: 'livro_remover', methods: ['POST'], requirements: ['codl' => '\d+'])]
    public function remover(
        #[MapEntity(id: 'codl')] Livro $livro,
    ): Response {
        $this->livroService->remover($livro);

        return $this->redirectToRoute('livro_index');
    }

}