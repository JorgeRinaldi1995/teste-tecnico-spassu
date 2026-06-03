<?php

namespace App\Controller;

use App\Entity\Livro;
use App\Repository\LivroRepository;
use App\Form\LivroType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

class LivroController extends AbstractController
{
    #[Route('/', name: 'livro_index', methods: ['GET'])]
    public function index(LivroRepository $livroRepository): Response
    {
        return $this->render('livro/index.html.twig', [
            'livros' => $livroRepository->findAllWithRelations(),
        ]);
    }

    #[Route('/novo', name: 'livro_novo', methods: ['GET', 'POST'])]
    public function novo(Request $request, LivroRepository $livroRepository): Response {
        $livro = new Livro();

        $form = $this->createForm(LivroType::class, $livro);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $livroRepository->save($livro, true);

            return $this->redirectToRoute('livro_index');
        }

        return $this->render('livro/novo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{codl}', name: 'livro_show', methods: ['GET'])]
    public function show(
        #[MapEntity(id: 'codl')]
        int $codl,
        LivroRepository $livroRepository
    ): Response
    {
        $livro = $livroRepository->findWithRelations($codl);

        if (!$livro) {
            throw $this->createNotFoundException('Livro não encontrado.');
        }

        return $this->render('livro/show.html.twig', [
            'livro' => $livro,
        ]);
    }

    #[Route('/{codl}/editar', name: 'livro_editar', methods: ['GET', 'POST'])]
    public function editar(
        #[MapEntity(id: 'codl')]
        Livro $livro,
        Request $request, 
        LivroRepository $livroRepository
    ): Response {
        $form = $this->createForm(LivroType::class, $livro);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $livroRepository->save($livro, true);

            return $this->redirectToRoute('livro_index');
        }

        return $this->render('livro/editar.html.twig', [
            'form' => $form->createView(),
            'livro' => $livro,
        ]);
    }

    #[Route('/{codl}/remover', name: 'livro_remover', methods: ['POST'])]
    public function remover(
        #[MapEntity(id: 'codl')]
        Livro $livro, 
        LivroRepository $livroRepository
    ): Response {
        $livroRepository->remove($livro, true);

        return $this->redirectToRoute('livro_index');
    }

}