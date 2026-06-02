<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Form\AutorType;
use App\Repository\AutorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/autor')]
class AutorController extends AbstractController
{
    #[Route('/', name: 'autor_index', methods: ['GET'])]
    public function index(AutorRepository $repository): Response
    {
        return $this->render('autor/index.html.twig', [
            'autores' => $repository->findAll(),
        ]);
    }

    #[Route('/novo', name: 'autor_novo', methods: ['GET', 'POST'])]
    public function novo(
        Request $request,
        AutorRepository $repository
    ): Response {
        $autor = new Autor();

        $form = $this->createForm(AutorType::class, $autor);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $repository->save($autor);

            return $this->redirectToRoute('autor_index');
        }

        return $this->render('autor/novo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{codau}', name: 'autor_show', methods: ['GET'])]
    public function show(
        #[MapEntity(id: 'codau')]
        Autor $autor
    ): Response
    {
        return $this->render('autor/show.html.twig', [
            'autor' => $autor,
        ]);
    }

    #[Route('/{id}/editar', name: 'autor_editar', methods: ['GET', 'POST'])]
    public function editar(
        Request $request,
        Autor $autor,
        AutorRepository $repository
    ): Response {
        $form = $this->createForm(AutorType::class, $autor);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $repository->save($autor);

            return $this->redirectToRoute('autor_index');
        }

        return $this->render('autor/editar.html.twig', [
            'form' => $form->createView(),
            'autor' => $autor,
        ]);
    }

    #[Route('/{id}/remover', name: 'autor_remover', methods: ['POST'])]
    public function remover(
        Autor $autor,
        AutorRepository $repository
    ): Response {
        $repository->remove($autor);

        return $this->redirectToRoute('autor_index');
    }
}