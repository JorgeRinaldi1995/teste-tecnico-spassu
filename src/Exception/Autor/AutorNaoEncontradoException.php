<?php

namespace App\Exception\Autor;

class AutorNaoEncontradoException extends \RuntimeException
{
    public function __construct(int $codigo)
    {
        parent::__construct(
            sprintf('Autor %d não encontrado.', $codigo)
        );
    }
}