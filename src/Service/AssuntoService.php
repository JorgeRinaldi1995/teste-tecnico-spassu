<?php

namespace App\Service;

use App\Entity\Assunto;
use App\Repository\AssuntoRepository;

class AssuntoService
{
    public function __construct(
        private readonly AssuntoRepository $repository
    ) {
    }

    /**
     * @return Assunto[]
     */
    public function listarAssuntos(): array
    {
        return $this->repository->findAllAssuntos();
    }

    public function buscarPorCodigo(int $codas): ?Assunto
    {
        return $this->repository->findById($codas);
    }

    public function criar(Assunto $assunto): void
    {
        $this->validarAssunto($assunto);

        $this->repository->save($assunto, true);
    }

    public function atualizar(Assunto $assunto): void
    {
        $this->validarAssunto($assunto);

        $this->repository->save($assunto, true);
    }

    public function remover(Assunto $assunto): void
    {
        $this->repository->remove($assunto, true);
    }

    private function validarAssunto(Assunto $assunto): void
    {
        if (trim($assunto->getDescricao()) === '') {
            throw new \DomainException(
                'A descrição do assunto é obrigatória.'
            );
        }
    }
}