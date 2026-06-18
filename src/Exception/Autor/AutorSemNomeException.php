<?php

namespace App\Exception\Autor;

class AutorSemNomeException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'O autor deve possuir um nome.'
        );
    }
}