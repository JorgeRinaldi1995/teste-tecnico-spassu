<?php

namespace App\DTO;

/**
 * Resultado paginado de uma consulta.
 *
 * @template T
 */

final class PaginatedResult
{
    public function __construct(
        public readonly array $data,
        public readonly int   $total,
        public readonly int   $pages,
    ) {}
}