<?php

namespace App\Exception\Livro;

class PaginacaoInvalidaException extends \DomainException
{
    private function __construct(string $mensagem)
    {
        parent::__construct($mensagem);
    }

    public static function porPaginaMenorQueUm(): self
    {
        return new self('A página deve ser maior que zero.');
    }

    public static function porLimiteForaDoIntervalo(int $limiteMaximo): self
    {
        return new self(
            sprintf('O limite deve estar entre 1 e %d.', $limiteMaximo)
        );
    }
}