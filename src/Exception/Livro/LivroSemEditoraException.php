<?php

namespace App\Exception\Livro;

class LivroSemEditoraException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'O livro deve possuir uma editora.'
        );
    }
}