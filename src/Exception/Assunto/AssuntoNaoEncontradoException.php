<?php

namespace App\Exception\Assunto;

class AssuntoNaoEncontradoException extends \RuntimeException
{
    public function __construct(int $codigo)
    {
        parent::__construct(
            sprintf('Assunto %d não encontrado.', $codigo)
        );
    }
}