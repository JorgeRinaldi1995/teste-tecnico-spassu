<?php

namespace App\Service;

use App\Entity\Livro;
use App\Repository\LivroRepository;

class LivroService
{
    public const LIVROS_POR_PAGINA = 20;

    public function __construct(
        private readonly LivroRepository $livroRepository
    ) {
    }

    public function listarTodos(int $pagina = 1, int $limite = self::LIVROS_POR_PAGINA): array
    {
        $offset = ($pagina - 1) * $limite;

        return $this->livroRepository->findAllWithRelations(
            limit: $limite,
            offset: $offset
        );
    }

    public function buscarPorCodigo(int $codl): ?Livro
    {
        return $this->livroRepository->findWithRelations($codl);
    }

    public function criar(Livro $livro): void
    {
        $this->validarLivro($livro);

        $this->livroRepository->save($livro, true);
    }

    public function atualizar(Livro $livro): void
    {
        $this->validarLivro($livro);

        $this->livroRepository->save($livro, true);
    }

    public function remover(Livro $livro): void
    {
        $this->livroRepository->remove($livro, true);
    }

    public function listarPorAutor(int $codau): array
    {
        return $this->livroRepository->findByAutor($codau);
    }

    public function listarPorAssunto(int $codas): array
    {
        return $this->livroRepository->findByAssunto($codas);
    }

    private function validarLivro(Livro $livro): void
    {
        if ($livro->getAutores()->isEmpty()) {
            throw new \DomainException(
                'O livro deve possuir ao menos um autor.'
            );
        }

        if ($livro->getAssuntos()->isEmpty()) {
            throw new \DomainException(
                'O livro deve possuir ao menos um assunto.'
            );
        }
    }
}