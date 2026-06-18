<?php

namespace App\Service;

use App\Entity\Livro;
use App\Repository\LivroRepository;
use App\Exception\Livro\LivroInvalidoException;
use App\Exception\Livro\AnoPublicacaoInvalidoException;
use App\Exception\Livro\LivroSemAutorException;
use App\Exception\Livro\LivroSemAssuntoException;
use App\Exception\Livro\LivroNaoEncontradoException;
use App\Exception\Livro\LivroSemTituloException;
use App\Exception\Livro\LivroSemEditoraException;
use App\Exception\Livro\LivroSemEdicaoException;
use App\Exception\Livro\LivroSemValorException;
use App\DTO\PaginatedResult;

/**
 * Serviço responsável pelas regras de negócio relacionadas aos livros.
 *
 * Centraliza operações de consulta, criação, atualização e remoção
 * de livros, além de aplicar validações de domínio antes da persistência.
 * 
 * @package App\Service
 */
class LivroService
{
    /**
     * Quantidade padrão de livros exibidos por página.
     */
    public const LIVROS_POR_PAGINA = 20;

    /**
     * Quantidade máxima de livros permitida por página.
     */
    private const LIMITE_MAXIMO_POR_PAGINA = 100;

    public function __construct(
        private readonly LivroRepository $livroRepository
    ) {
    }

    /**
     * Retorna uma lista paginada de livros com seus autores e assuntos.
     *
     * @param int $pagina Número da página desejada (mínimo: 1).
     * @param int $limite Quantidade de registros por página (entre 1 e 100).
     *
     * @return PaginatedResult<Livro>
     * 
     * @throws \InvalidArgumentException Se $pagina < 1 ou $limite fora do intervalo [1, 100].
     * 
     */
    public function listarTodos(
        int $pagina = 1,
        int $limite = self::LIVROS_POR_PAGINA
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
        $resultado = $this->livroRepository->findAllWithRelations(
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
     * Busca um livro pelo código primário, carregando autores e assuntos.
     *
     * @param int $codl Código (PK) do livro.
     *
     * @return Livro Livro encontrado.
     */
    public function buscarPorCodigo(int $codl): Livro
    {
        $livro = $this->livroRepository->findWithRelations($codl);

        return $livro;
    }

    /**
     * Cria um novo livro aplicando validações de domínio antes da persistência.
     *
     * @param Livro $livro Entidade a ser persistida.
     *
     * @return void
     *
     * @throws LivroInvalidoException Se uma ou mais regras de negócio forem violadas.
     */
    public function criar(Livro $livro): void
    {
        $this->validarLivro($livro);

        $this->livroRepository->save($livro, true);
    }

    /**
     * Atualiza um livro existente aplicando validações de domínio antes da persistência.
     *
     * @param Livro $livro Entidade a ser atualizada.
     *
     * @return void
     *
     * @throws LivroInvalidoException Se uma ou mais regras de negócio forem violadas.
     */
    public function atualizar(Livro $livro): void
    {
        $this->validarLivro($livro);

        $this->livroRepository->save($livro, true);
    }

    /**
     * Remove um livro da base de dados.
     *
     * @param Livro $livro Entidade a ser removida.
     *
     * @return void
     */
    public function remover(Livro $livro): void
    {
        $this->livroRepository->remove($livro, true);
    }

    /**
     * Retorna todos os livros associados a um autor.
     *
     * @param int $codau Código (PK) do autor.
     *
     * @return Livro[] Lista de livros do autor. Retorna array vazio se não houver nenhum.
     */
    public function listarPorAutor(int $codau): array
    {
        return $this->livroRepository->findByAutor($codau);
    }

    /**
     * Retorna todos os livros associados a um assunto.
     *
     * @param int $codas Código (PK) do assunto.
     *
     * @return Livro[] Lista de livros do assunto. Retorna array vazio se não houver nenhum.
     */
    public function listarPorAssunto(int $codas): array
    {
        return $this->livroRepository->findByAssunto($codas);
    }

    /**
     * Valida as regras de negócio de um livro.
     *
     * Coleta todas as violações antes de lançar a exceção, garantindo que
     * o chamador receba o conjunto completo de erros em uma única chamada.
     *
     * Regras aplicadas:
     * - Título não pode ser vazio ou conter apenas espaços.
     * - Editora não pode ser vazia ou conter apenas espaços.
     * - Edição não pode ser vazia ou conter apenas espaços.
     * - Ano de publicação não pode ser superior ao ano corrente.
     * - Valor deve ser maior que zero.
     * - O livro deve possuir ao menos um autor.
     * - O livro deve possuir ao menos um assunto.
     *
     * @param Livro $livro Entidade a ser validada.
     *
     * @return void
     *
     * @throws LivroInvalidoException Agrega todas as violações encontradas.
     */
    private function validarLivro(Livro $livro): void
    {
        $violacoes = [];

        if (empty(trim($livro->getTitulo()))) {
            $violacoes[] = new LivroSemTituloException();
        }

        if (empty(trim($livro->getEditora()))) {
            $violacoes[] = new LivroSemEditoraException();
        }

        if ($livro->getEdicao() <= 0){
            $violacoes[] = new LivroSemEdicaoException();
        }
        
        if ($livro->getAnoPublicacao() <= 0) {
            $violacoes[] = AnoPublicacaoInvalidoException::porNaoSerPositivo();
        }

        if ($livro->getAnoPublicacao() > (int) date('Y')) {
            $violacoes[] = AnoPublicacaoInvalidoException::porUltrapassarAnoAtual();
        }

        if ($livro->getValor() <= 0) {
            $violacoes[] = new LivroSemValorException();
        }

        if ($livro->getAutores()->isEmpty()) {
            $violacoes[] = new LivroSemAutorException();
        }

        if ($livro->getAssuntos()->isEmpty()) {
            $violacoes[] = new LivroSemAssuntoException();
        }

        if (!empty($violacoes)) {
            throw new LivroInvalidoException($violacoes);
        }
    }
}