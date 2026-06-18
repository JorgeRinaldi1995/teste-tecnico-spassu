<?php

namespace App\Exception\Livro;

class AnoPublicacaoInvalidoException extends \DomainException
{
    private function __construct(string $mensagem)
    {
        parent::__construct($mensagem);
    }

    public static function porNaoSerPositivo(): self
    {
        return new self('O ano de publicação deve ser um valor positivo.');
    }

    public static function porUltrapassarAnoAtual(): self
    {
        return new self('O ano de publicação não pode ser maior que o ano atual.');
    }
}