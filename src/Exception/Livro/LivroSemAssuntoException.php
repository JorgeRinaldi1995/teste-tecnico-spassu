<?php

namespace App\Exception\Livro;

class LivroSemAssuntoException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'O livro deve possuir ao menos um assunto.'
        );
    }
}