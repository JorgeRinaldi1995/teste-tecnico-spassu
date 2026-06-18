<?php

namespace App\Exception\Assunto;

class AssuntoSemDescricaoException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'O assunto deve possuir uma descrição.'
        );
    }
}