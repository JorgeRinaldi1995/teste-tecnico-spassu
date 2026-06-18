<?php

namespace App\Service;

use App\Entity\Autor;
use App\Repository\AutorRepository;
use App\Exception\Autor\AutorInvalidoException;
use App\Exception\Autor\AutorNaoEncontradoException;
use App\Exception\Autor\AutorSemNomeException;
use App\DTO\PaginatedResult;

/**
 * Serviço responsável pelas regras de negócio relacionadas aos Autores.
 *
 * Centraliza operações de consulta, criação, atualização e remoção
 * de autores, além de aplicar validações de domínio antes da persistência.
 * 
 * @package App\Service
 */
class AutorService
{
    /**
     * Quantidade padrão de autor exibidos por página.
     */
    public const AUTORES_POR_PAGINA = 20;

    /**
     * Quantidade máxima de autores permitida por página.
     */
    private const LIMITE_MAXIMO_POR_PAGINA = 100;

    public function __construct(
        private readonly AutorRepository $repository
    ) {
    }

    /**
     * Retorna uma lista paginada de autores.
     *
     * @param int $pagina Número da página desejada (mínimo: 1).
     * @param int $limite Quantidade de registros por página (entre 1 e 100).
     *
     * @return PaginatedResult<Autor>
     * 
     * @throws \InvalidArgumentException Se $pagina < 1 ou $limite fora do intervalo [1, 100].
     * 
     */
    public function listarTodos(
        int $pagina = 1,
        int $limite = self::AUTORES_POR_PAGINA
    ): PaginatedResult {
        if ($pagina < 1) {
            throw new \InvalidArgumentException('A página deve ser maior que zero.');
        }

        if ($limite < 1 || $limite > self::LIMITE_MAXIMO_POR_PAGINA) {
             throw new \InvalidArgumentException(
                sprintf('O limite deve estar entre 1 e %d.', self::LIMITE_MAXIMO_POR_PAGINA)
            );
        }

        $offset    = ($pagina - 1) * $limite;
        $resultado = $this->repository->findAll(
            limit:  $limite,
            offset: $offset,
        );
        
        return new PaginatedResult(
            data:  $resultado['data'],
            total: $resultado['total'],
            pages: (int) ceil($resultado['total'] / $limite),
        );
    }

    /**
     * Busca um autor pelo código primário.
     *
     * @param int $codau Código (PK) do Autor.
     *
     * @return Autor Autor encontrado.
     *
     * @throws AutorNaoEncontradoException Se nenhum autor for encontrado com o código informado.
     *
     */
    public function buscarPorCodigo(int $codau): ?Autor
    {
        $autor = $this->repository->findById($codau);

        if (!$autor) {
            throw new AutorNaoEncontradoException($codau);
        }

        return $autor;
    }

    /**
     * Cria um novo autor aplicando validações de domínio antes da persistência.
     *
     * @param Autor $autor Entidade a ser persistida.
     *
     * @return void
     *
     * @throws AutorInvalidoException Se uma ou mais regras de negócio forem violadas.
     */
    public function criar(Autor $autor): void
    {
        $this->validarAutor($autor);

        $this->repository->save($autor, true);
    }

    /**
     * Atualiza um novo autor aplicando validações de domínio antes da persistência.
     *
     * @param Autor $autor Entidade a ser persistida.
     *
     * @return void
     *
     * @throws AutorInvalidoException Se uma ou mais regras de negócio forem violadas.
     */
    public function atualizar(Autor $autor): void
    {
        $this->validarAutor($autor);

        $this->repository->save($autor, true);
    }

    /**
     * Remove um autor da base de dados.
     *
     * @param Autor $autor Entidade a ser removida.
     *
     * @return void
     */
    public function remover(Autor $autor): void
    {
        if (!$autor->getLivros()->isEmpty()) {
            throw new \DomainException(
                'Não é possível remover um autor vinculado a livros.'
            );
        }

        $this->repository->remove($autor, true);
    }

    /**
     * Valida as regras de negócio de um autor.
     *
     * Coleta todas as violações antes de lançar a exceção, garantindo que
     * o chamador receba o conjunto completo de erros em uma única chamada.
     *
     * Regras aplicadas:
     * - Nome não pode ser vazia ou conter apenas espaços.
     *
     * @param Autor $autor Entidade a ser validada.
     *
     * @return void
     *
     * @throws AutorInvalidoException Agrega todas as violações encontradas.
     */
    private function validarAutor(Autor $autor): void
    {
        $violacoes = [];

        if (empty(trim($autor->getNome()))) {
            $violacoes[] = new AutorSemNomeException();
        }

        if (!empty($violacoes)) {
            throw new AutorInvalidoException($violacoes);
        }
    }
}