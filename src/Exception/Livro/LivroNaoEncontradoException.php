<?php

namespace App\Exception\Livro;

class LivroNaoEncontradoException extends \RuntimeException
{
    public function __construct(int $codigo)
    {
        parent::__construct(
            sprintf('Livro %d não encontrado.', $codigo)
        );
    }
}