<?php

namespace App\Exception\Autor;

class AutorInvalidoException extends \DomainException
{
    /** @param list<\DomainException> $violacoes */
    public function __construct(
        private readonly array $violacoes
    ) {
        parent::__construct('O autor contém erros de validação.');
    }

    /** @return list<\DomainException> */
    public function getViolacoes(): array
    {
        return $this->violacoes;
    }
}