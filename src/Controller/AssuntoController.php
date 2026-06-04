<?php

namespace App\Controller;

use App\Entity\Assunto;
use App\Form\AssuntoType;
use App\Repository\AssuntoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/assunto')]
class AssuntoController extends AbstractController
{
    public function __construct(
        private readonly AssuntoRepository $repository
    ){

    }

    #[Route('/', name: 'assunto_index', methods: ['GET'])]
    public function index(AssuntoRepository $repository): Response
    {
        return $this->render('assunto/index.html.twig', [
            'assuntos' => $this->repository->findAssuntosAtivos(),
        ]);
    }

    #[Route('/novo', name: 'assunto_novo', methods: ['GET', 'POST'])]
    public function novo(
        Request $request,
        AssuntoRepository $repository
    ): Response {
        $assunto = new Assunto();

        $form = $this->createForm(AssuntoType::class, $assunto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->repository->save($assunto, true);

            return $this->redirectToRoute('assunto_index');
        }

        return $this->render('assunto/novo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{codas}', name: 'assunto_show', methods: ['GET'])]
    public function show(
        #[MapEntity(id: 'codas')]
        int $codas,
        Assunto $assunto
    ): Response {
        $assunto = $this->repository->findById($codas);
        if (!$assunto){
            throw $this->createNotFoundException(
                'Assunto não encontrado.'
            );
        }
        return $this->render('assunto/show.html.twig', [
            'assunto' => $assunto,
        ]);
    }

    #[Route('/{codas}/editar', name: 'assunto_editar', methods: ['GET', 'POST'])]
    public function editar(
        #[MapEntity(id: 'codas')]
        Assunto $assunto,
        Request $request,
        AssuntoRepository $repository
    ): Response {
        $form = $this->createForm(AssuntoType::class, $assunto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $repository->save($assunto, true);
            $this->addFlash('success', 'Assunto salvo com sucesso.');
            return $this->redirectToRoute('assunto_index');
        }

        return $this->render('assunto/editar.html.twig', [
            'form' => $form->createView(),
            'assunto' => $assunto,
        ]);
    }

    #[Route('/{codas}/remover', name: 'assunto_remover', methods: ['POST'])]
    public function remover(
        #[MapEntity(id: 'codas')]
        Assunto $assunto,
        AssuntoRepository $repository
    ): Response {
        $repository->remove($assunto, true);

        return $this->redirectToRoute('assunto_index');
    }
}