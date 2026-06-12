<?php

namespace App\Exception\Livro;

class LivroSemValorException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'O livro deve possuir um valor válido.'
        );
    }
}