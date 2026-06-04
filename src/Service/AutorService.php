<?php

namespace App\Service;

use App\Entity\Autor;
use App\Repository\AutorRepository;

class AutorService
{
    public function __construct(
        private readonly AutorRepository $repository
    ) {
    }

    /**
     * @return Autor[]
     */
    public function listarTodos(): array
    {
        return $this->repository->findAll();
    }

    public function buscarPorCodigo(int $codau): ?Autor
    {
        return $this->repository->findById($codau);
    }

    public function criar(Autor $autor): void
    {
        $this->validarAutor($autor);

        $this->repository->save($autor, true);
    }

    public function atualizar(Autor $autor): void
    {
        $this->validarAutor($autor);

        $this->repository->save($autor, true);
    }

    public function remover(Autor $autor): void
    {
        if (!$autor->getLivros()->isEmpty()) {
            throw new \DomainException(
                'Não é possível remover um autor vinculado a livros.'
            );
        }

        $this->repository->remove($autor, true);
    }

    private function validarAutor(Autor $autor): void
    {
        if (trim($autor->getNome()) === '') {
            throw new \DomainException(
                'O nome do autor é obrigatório.'
            );
        }
    }
}