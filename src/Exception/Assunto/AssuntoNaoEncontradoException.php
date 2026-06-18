<?php

namespace App\Exception\Assunto;

class AssuntoNaoEncontradoException extends \DomainException
{
    /** @param list<\DomainException> $violacoes */
    public function __construct(
        private readonly array $violacoes
    ) {
        parent::__construct('Assunto não encontrado.');
    }

    /** @return list<\DomainException> */
    public function getViolacoes(): array
    {
        return $this->violacoes;
    }
}