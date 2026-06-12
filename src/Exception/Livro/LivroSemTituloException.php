<?php

namespace App\Exception\Livro;

class LivroSemTituloException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'O livro deve possuir um título.'
        );
    }
}