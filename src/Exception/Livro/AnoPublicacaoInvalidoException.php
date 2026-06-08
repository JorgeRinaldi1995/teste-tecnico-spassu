<?php

namespace App\Exception\Livro;

class AnoPublicacaoInvalidoException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'O ano de publicação não pode ser maior que o ano atual.'
        );
    }
}