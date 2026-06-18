<?php

namespace App\Exception\Assunto;

class AssuntoInvalidoException extends \DomainException
{
    /** @param list<\DomainException> $violacoes */
    public function __construct(
        private readonly array $violacoes
    ) {
        parent::__construct('O assunto contém erros de validação.');
    }

    /** @return list<\DomainException> */
    public function getViolacoes(): array
    {
        return $this->violacoes;
    }
}