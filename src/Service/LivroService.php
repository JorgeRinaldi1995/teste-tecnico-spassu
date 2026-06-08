<?php

namespace App\Service;

use App\Entity\Livro;
use App\Repository\LivroRepository;
use App\Exception\Livro\AnoPublicacaoInvalidoException;
use App\Exception\Livro\LivroSemAutorException;
use App\Exception\Livro\LivroSemAssuntoException;
use App\Exception\Livro\LivroNaoEncontradoException;

/**
 * Serviço responsável pelas regras de negócio relacionadas aos livros.
 *
 * Centraliza operações de consulta, criação, atualização e remoção
 * de livros, além de aplicar validações de domínio antes da persistência.
 */
class LivroService
{
    /**
     * Quantidade padrão de livros exibidos por página.
     */
    public const LIVROS_POR_PAGINA = 20;

    public function __construct(
        private readonly LivroRepository $livroRepository
    ) {
    }

    /**
     * Retorna uma lista paginada de livros com seus autores e assuntos.
     *
     * @param int $pagina Número da página desejada.
     * @param int $limite Quantidade de registros por página.
     *
     * @return array{
     *     data: Livro[],
     *     total: int,
     *     pages: int
     * }
     */
    public function listarTodos(
        int $pagina = 1,
        int $limite = self::LIVROS_POR_PAGINA
    ): array {
        if ($limite <= 0) {
            throw new \InvalidArgumentException(
                'O limite deve ser maior que zero.'
            );
        }

        $offset = ($pagina - 1) * $limite;

        if ($offset < 0) {
            throw new \InvalidArgumentException(
                'O offset não pode ser negativo.'
            );
        }

        return $this->livroRepository->findAllWithRelations(
            limit: $limite,
            offset: $offset
        );
    }

    /**
     * Busca um livro pelo código primário carregando autores e assuntos.
     *
     * @param int $codl Código do livro.
     *
     * @return Livro|null Retorna o livro encontrado ou null.
     */
    public function buscarPorCodigo(int $codl): ?Livro
    {
        $livro = $this->livroRepository->findWithRelations($codl);

        if (!$livro) {
            throw new LivroNaoEncontradoException($codl);
        }

        return $livro;
    }

    /**
     * Cria um novo livro.
     *
     * Antes da persistência são executadas validações de domínio.
     *
     * @param Livro $livro Entidade a ser persistida.
     *
     * @throws \DomainException Quando alguma regra de negócio é violada.
     */
    public function criar(Livro $livro): void
    {
        $this->validarLivro($livro);

        $this->livroRepository->save($livro, true);
    }

    /**
     * Atualiza um livro existente.
     *
     * Antes da persistência são executadas validações de domínio.
     *
     * @param Livro $livro Entidade a ser atualizada.
     *
     * @throws \DomainException Quando alguma regra de negócio é violada.
     */
    public function atualizar(Livro $livro): void
    {
        $this->validarLivro($livro);

        $this->livroRepository->save($livro, true);
    }

    /**
     * Remove um livro.
     *
     * @param Livro $livro Entidade a ser removida.
     */
    public function remover(Livro $livro): void
    {
        $this->livroRepository->remove($livro, true);
    }

    /**
     * Retorna todos os livros associados a um autor.
     *
     * @param int $codau Código do autor.
     *
     * @return Livro[]
     */
    public function listarPorAutor(int $codau): array
    {
        return $this->livroRepository->findByAutor($codau);
    }

    /**
     * Retorna todos os livros associados a um assunto.
     *
     * @param int $codas Código do assunto.
     *
     * @return Livro[]
     */
    public function listarPorAssunto(int $codas): array
    {
        return $this->livroRepository->findByAssunto($codas);
    }

    /**
     * Valida as regras de negócio de um livro.
     *
     * Regras:
     * - O ano de publicação não pode ser maior que o ano atual.
     * - O livro deve possuir ao menos um autor.
     * - O livro deve possuir ao menos um assunto.
     *
     * @param Livro $livro Entidade a ser validada.
     *
     * @throws \DomainException Quando alguma regra de negócio é violada.
     */
    private function validarLivro(Livro $livro): void
    {
        $anoAtual = (int) date('Y');

        if ($livro->getAnoPublicacao() > $anoAtual) {
            throw new AnoPublicacaoInvalidoException();
        }

        if ($livro->getAutores()->isEmpty()) {
            throw new LivroSemAutorException();
        }

        if ($livro->getAssuntos()->isEmpty()) {
            throw new LivroSemAssuntoException();
        }
    }
}