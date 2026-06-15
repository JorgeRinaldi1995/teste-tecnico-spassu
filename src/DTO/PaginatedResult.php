<?php

namespace App\DTO;

/**
 * Resultado paginado de uma consulta.
 *
 * @template T
 */

final class PaginatedResult
{
    /**
     * @var list<T>
     */
    public readonly array $data;

    /**
     * @param list<T> $data
     */
    public function __construct(
        array $data,
        public readonly int $total,
        public readonly int $pages,
    ) {
        $this->data = $data;
    }
}