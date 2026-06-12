<?php

namespace App\Exception\Livro;

class LivroInvalidoException extends \DomainException
{
    /** @param list<\DomainException> $violacoes */
    public function __construct(
        private readonly array $violacoes
    ) {
        parent::__construct('O livro contém erros de validação.');
    }

    /** @return list<\DomainException> */
    public function getViolacoes(): array
    {
        return $this->violacoes;
    }
}