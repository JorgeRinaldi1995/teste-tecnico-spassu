<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Form\AutorType;
use App\Repository\AutorRepository;
use App\Service\AutorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/autor')]
class AutorController extends AbstractController
{
    public function __construct(
        private readonly AutorService $autorService
    ) {
    }
    #[Route('/', name: 'autor_index', methods: ['GET'])]
    public function index(AutorRepository $repository): Response
    {
        return $this->render('autor/index.html.twig', [
            'autores' => $this->autorService->listarTodos(),
        ]);
    }

    #[Route('/novo', name: 'autor_novo', methods: ['GET', 'POST'])]
    public function novo(
        Request $request,
        AutorRepository $repository,
    ): Response {
        $autor = new Autor();

        $form = $this->createForm(AutorType::class, $autor);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->autorService->criar($autor);

            return $this->redirectToRoute('autor_index');
        }

        return $this->render('autor/novo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{codau}', name: 'autor_show', methods: ['GET'])]
    public function show(int $codau): Response
    {
        $autor = $this->autorService->buscarPorCodigo($codau);

        if (!$autor) {
            throw $this->createNotFoundException(
                'Autor não encontrado.'
            );
        }

        return $this->render('autor/show.html.twig', [
            'autor' => $autor,
        ]);
    }

    #[Route('/{codau}/editar', name: 'autor_editar', methods: ['GET', 'POST'])]
    public function editar(
        #[MapEntity(id: 'codau')] 
        Autor $autor,
        Request $request,
        AutorRepository $repository
    ): Response {
        $form = $this->createForm(AutorType::class, $autor);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->autorService->atualizar($autor);

            $this->addFlash(
                'success',
                'Autor salvo com sucesso.'
            );

            return $this->redirectToRoute('autor_index');
        }

        return $this->render('autor/editar.html.twig', [
            'form' => $form->createView(),
            'autor' => $autor,
        ]);
    }

    #[Route('/{codau}/remover', name: 'autor_remover', methods: ['POST'])]
    public function remover(
        #[MapEntity(id: 'codau')] 
        Autor $autor,
        AutorRepository $repository
    ): Response {
        try {

            $this->autorService->remover($autor);

            $this->addFlash(
                'success',
                'Autor removido com sucesso.'
            );

        } catch (\DomainException $e) {

            $this->addFlash(
                'danger',
                $e->getMessage()
            );
        }

        return $this->redirectToRoute('autor_index');
    }
}