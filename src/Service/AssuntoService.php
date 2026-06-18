<?php

namespace App\Service;

use App\Entity\Assunto;
use App\Repository\AssuntoRepository;
use App\Exception\Assunto\AssuntoInvalidoException;
use App\Exception\Assunto\AssuntoNaoEncontradoException;
use App\Exception\Assunto\AssuntoSemDescricaoException;
use App\DTO\PaginatedResult;

/**
 * Serviço responsável pelas regras de negócio relacionadas aos Assuntos.
 *
 * Centraliza operações de consulta, criação, atualização e remoção
 * de assuntos, além de aplicar validações de domínio antes da persistência.
 * 
 * @package App\Service
 */
class AssuntoService
{
    /**
     * Quantidade padrão de assuntos exibidos por página.
     */
    public const ASSUNTOS_POR_PAGINA = 20;

    /**
     * Quantidade máxima de assuntos permitida por página.
     */
    private const LIMITE_MAXIMO_POR_PAGINA = 100;

    public function __construct(
        private readonly AssuntoRepository $repository
    ) {
    }

    /**
     * Retorna uma lista paginada de autores.
     *
     * @param int $pagina Número da página desejada (mínimo: 1).
     * @param int $limite Quantidade de registros por página (entre 1 e 100).
     *
     * @return PaginatedResult<Assunto>
     * 
     * @throws \InvalidArgumentException Se $pagina < 1 ou $limite fora do intervalo [1, 100].
     * 
     */
    public function listarAssuntos(
        int $pagina = 1,
        int $limite = self::ASSUNTOS_POR_PAGINA
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
        $resultado = $this->repository->findAllAssuntos(
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
     * Busca um assunto pelo código primário.
     *
     * @param int $codas Código (PK) do Assunto.
     *
     * @return Assunto Assunto encontrado.
     *
     * @throws AssuntoNaoEncontradoException Se nenhum assunto for encontrado com o código informado.
     *
     */
    public function buscarPorCodigo(int $codas): ?Assunto
    {
        return $assunto = $this->repository->findById($codas);

        if (!$assunto) {
            throw new AssuntoNaoEncontradoException($codas);
        }

        return $assunto;
    }

    /**
     * Cria um novo assunto aplicando validações de domínio antes da persistência.
     *
     * @param Assunto $assunto Entidade a ser persistida.
     *
     * @return void
     *
     * @throws AssuntoInvalidoException Se uma ou mais regras de negócio forem violadas.
     */
    public function criar(Assunto $assunto): void
    {
        $this->validarAssunto($assunto);

        $this->repository->save($assunto, true);
    }

    /**
     * Atualiza um assunto existente aplicando validações de domínio antes da persistência.
     *
     * @param Assunto $assunto Entidade a ser atualizada.
     *
     * @return void
     *
     * @throws AssuntoInvalidoException Se uma ou mais regras de negócio forem violadas.
     */
    public function atualizar(Assunto $assunto): void
    {
        $this->validarAssunto($assunto);

        $this->repository->save($assunto, true);
    }

    /**
     * Remove um assunto da base de dados.
     *
     * @param Assunto $assunto Entidade a ser removida.
     *
     * @return void
     */
    public function remover(Assunto $assunto): void
    {
        $this->repository->remove($assunto, true);
    }

    /**
     * Valida as regras de negócio de um assunto.
     *
     * Coleta todas as violações antes de lançar a exceção, garantindo que
     * o chamador receba o conjunto completo de erros em uma única chamada.
     *
     * Regras aplicadas:
     * - Descrição não pode ser vazia ou conter apenas espaços.
     *
     * @param Assunto $assunto Entidade a ser validada.
     *
     * @return void
     *
     * @throws AssuntoInvalidoException Agrega todas as violações encontradas.
     */
    private function validarAssunto(Assunto $assunto): void
    {
        $violacoes = [];

        if (empty(trim($assunto->getDescricao()))) {
            $violacoes[] = new AssuntoSemDescricaoException();
        }

        if (!empty($violacoes)) {
            throw new AssuntoInvalidoException($violacoes);
        }
    }
}