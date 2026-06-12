<?php

namespace App\Exception\Livro;

class LivroSemEdicaoException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'O livro deve possuir uma edição.'
        );
    }
}