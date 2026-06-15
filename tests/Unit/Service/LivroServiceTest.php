<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\PaginatedResult;
use App\Entity\Livro;
use App\Exception\Livro\AnoPublicacaoInvalidoException;
use App\Exception\Livro\LivroInvalidoException;
use App\Exception\Livro\LivroNaoEncontradoException;
use App\Exception\Livro\LivroSemAssuntoException;
use App\Exception\Livro\LivroSemAutorException;
use App\Exception\Livro\LivroSemEdicaoException;
use App\Exception\Livro\LivroSemEditoraException;
use App\Exception\Livro\LivroSemTituloException;
use App\Exception\Livro\LivroSemValorException;
use App\Repository\LivroRepository;
use App\Service\LivroService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Testes unitários para LivroService.
 *
 * Cobre: listarTodos, buscarPorCodigo, criar, atualizar,
 *        remover, listarPorAutor, listarPorAssunto e validarLivro.
 * 
 * @phpstan-type LivroBuildArgs array{
 *     titulo?: string,
 *     editora?: string,
 *     edicao?: int,
 *     anoPublicacao?: string,
 *     valor?: string,
 *     comAutor?: bool,
 *     comAssunto?: bool
 * }
 */
class LivroServiceTest extends TestCase
{
    private LivroRepository&MockObject $repositoryMock;
    private LivroService $service;

    // -------------------------------------------------------------------------
    // Setup
    // -------------------------------------------------------------------------

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(LivroRepository::class);
        $this->service        = new LivroService($this->repositoryMock);
    }

    // =========================================================================
    // Helpers / Builders
    // =========================================================================

    /**
     * Cria um Livro stub completamente válido, pronto para persistência.
     *
     * Todos os campos obrigatórios são preenchidos com valores válidos;
     * use os parâmetros nomeados para sobrescrever apenas o que for relevante
     * para o cenário em teste.
     */
    private function buildLivroValido(
        string $titulo         = 'PHP Moderno',
        string $editora        = 'Tech Books',
        int    $edicao         = 1,
        string $anoPublicacao  = '2020',
        string $valor          = '99.90',
        bool   $comAutor       = true,
        bool   $comAssunto     = true,
    ): Livro {
        $livro = $this->createMock(Livro::class);

        $livro->method('getTitulo')->willReturn($titulo);
        $livro->method('getEditora')->willReturn($editora);
        $livro->method('getEdicao')->willReturn($edicao);
        $livro->method('getAnoPublicacao')->willReturn($anoPublicacao);
        $livro->method('getValor')->willReturn($valor);
        $livro->method('getAutores')->willReturn(
            new ArrayCollection($comAutor ? [new \stdClass()] : [])
        );
        $livro->method('getAssuntos')->willReturn(
            new ArrayCollection($comAssunto ? [new \stdClass()] : [])
        );

        return $livro;
    }

    /**
     * Gera um resultado paginado fictício para o repositório.
     *
     * @param int $total Total de registros.
     * @param int $count Quantidade de livros no array de dados.
     * 
     * @return array{
     *      data: list<Livro>,
     *      total: int
     * }
     */
    private function buildRepositoryResult(int $total = 1, int $count = 1): array
    {
        return [
            'data'  => array_fill(
                0,
                $count,
                $this->createMock(Livro::class)
            ),
            'total' => $total,
        ];
    }

    // =========================================================================
    // listarTodos
    // =========================================================================

    /** @return array<string, array{pagina: int, limite: int}> */
    public static function listarTodosParametrosInvalidosProvider(): array
    {
        return [
            'página zero'     => ['pagina' => 0,  'limite' => 10],
            'página negativa' => ['pagina' => -1, 'limite' => 10],
            'limite zero'     => ['pagina' => 1,  'limite' => 0],
            'limite negativo' => ['pagina' => 1,  'limite' => -5],
            'limite acima de 100' => ['pagina' => 1, 'limite' => 101],
        ];
    }

    #[Test]
    #[DataProvider('listarTodosParametrosInvalidosProvider')]
    public function listarTodosLancaExcecaoParaParametrosInvalidos(
        int $pagina,
        int $limite
    ): void {
        $this->repositoryMock->expects($this->never())->method('findAllWithRelations');

        $this->expectException(\InvalidArgumentException::class);

        $this->service->listarTodos($pagina, $limite);
    }

    /** @return array<string, array{pagina: int, limite: int, totalRegistros: int, pagesEsperadas: int}> */
    public static function listarTodosCalculoPaginacaoProvider(): array
    {
        return [
            'uma página exata'     => ['pagina' => 1, 'limite' => 20, 'totalRegistros' => 20, 'pagesEsperadas' => 1],
            'duas páginas exatas'  => ['pagina' => 1, 'limite' => 10, 'totalRegistros' => 20, 'pagesEsperadas' => 2],
            'arredonda para cima'  => ['pagina' => 1, 'limite' => 10, 'totalRegistros' => 21, 'pagesEsperadas' => 3],
            'total zero'           => ['pagina' => 1, 'limite' => 20, 'totalRegistros' => 0,  'pagesEsperadas' => 0],
            'limite máximo (100)'  => ['pagina' => 1, 'limite' => 100,'totalRegistros' => 1,  'pagesEsperadas' => 1],
        ];
    }

    #[Test]
    #[DataProvider('listarTodosCalculoPaginacaoProvider')]
    public function listarTodosRetornaPaginatedResultComCalculoCorreto(
        int $pagina,
        int $limite,
        int $totalRegistros,
        int $pagesEsperadas
    ): void {
        $this->repositoryMock
            ->method('findAllWithRelations')
            ->willReturn($this->buildRepositoryResult($totalRegistros, min($limite, $totalRegistros)));

        $result = $this->service->listarTodos($pagina, $limite);

        $this->assertInstanceOf(PaginatedResult::class, $result);
        $this->assertSame($totalRegistros, $result->total);
        $this->assertSame($pagesEsperadas, $result->pages);
    }

    #[Test]
    public function listarTodosCalculaOffsetCorretamente(): void
    {
        $pagina = 3;
        $limite = 20;
        $offsetEsperado = ($pagina - 1) * $limite; // 40

        $this->repositoryMock
            ->expects($this->once())
            ->method('findAllWithRelations')
            ->with($this->equalTo($limite), $this->equalTo($offsetEsperado))
            ->willReturn($this->buildRepositoryResult(100, $limite));

        $this->service->listarTodos($pagina, $limite);
    }

    #[Test]
    public function listarTodosUsaValoresDefaultCorretamente(): void
    {
        $offsetEsperado = 0; // página 1, limite padrão

        $this->repositoryMock
            ->expects($this->once())
            ->method('findAllWithRelations')
            ->with(LivroService::LIVROS_POR_PAGINA, $offsetEsperado)
            ->willReturn($this->buildRepositoryResult(5, 5));

        $result = $this->service->listarTodos();

        $this->assertInstanceOf(PaginatedResult::class, $result);
    }

    // =========================================================================
    // buscarPorCodigo
    // =========================================================================

    #[Test]
    public function buscarPorCodigoRetornaLivroQuandoEncontrado(): void
    {
        $livroMock = $this->createMock(Livro::class);

        $this->repositoryMock
            ->expects($this->once())
            ->method('findWithRelations')
            ->with(42)
            ->willReturn($livroMock);

        $result = $this->service->buscarPorCodigo(42);

        $this->assertSame($livroMock, $result);
    }

    #[Test]
    public function buscarPorCodigoLancaExcecaoQuandoNaoEncontrado(): void
    {
        $this->repositoryMock
            ->method('findWithRelations')
            ->willReturn(null);

        $this->expectException(LivroNaoEncontradoException::class);

        $this->service->buscarPorCodigo(999);
    }

    // =========================================================================
    // criar / atualizar — caminho feliz
    // =========================================================================

    #[Test]
    public function criarPersisteLivroValido(): void
    {
        $livro = $this->buildLivroValido();

        $this->repositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($livro, true);

        $this->service->criar($livro);
    }

    #[Test]
    public function atualizarPersisteLivroValido(): void
    {
        $livro = $this->buildLivroValido();

        $this->repositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($livro, true);

        $this->service->atualizar($livro);
    }

    // =========================================================================
    // validarLivro — violações individuais
    // =========================================================================

    /**
     * Cada entrada define como montar um Livro inválido e qual exception
     * de violação deve estar presente no LivroInvalidoException agregado.
     *
     * @return array<string, array{buildArgs: array<string, mixed>, violacaoEsperada: class-string}>
     */
    public static function violacoesIndividuaisProvider(): array
    {
        $anoFuturo = (string) ((int) date('Y') + 1);

        return [
            'sem título'           => [
                'buildArgs'         => ['titulo' => ''],
                'violacaoEsperada'  => LivroSemTituloException::class,
            ],
            'título só espaços'   => [
                'buildArgs'         => ['titulo' => '   '],
                'violacaoEsperada'  => LivroSemTituloException::class,
            ],
            'sem editora'          => [
                'buildArgs'         => ['editora' => ''],
                'violacaoEsperada'  => LivroSemEditoraException::class,
            ],
            'editora só espaços'  => [
                'buildArgs'         => ['editora' => '  '],
                'violacaoEsperada'  => LivroSemEditoraException::class,
            ],
            'edição zero' => [
                'buildArgs' => ['edicao' => 0],
                'violacaoEsperada' => LivroSemEdicaoException::class,
            ],

            'edição negativa' => [
                'buildArgs' => ['edicao' => -1],
                'violacaoEsperada' => LivroSemEdicaoException::class,
            ],
            'ano futuro'           => [
                'buildArgs'         => ['anoPublicacao' => $anoFuturo],
                'violacaoEsperada'  => AnoPublicacaoInvalidoException::class,
            ],
            'valor zero' => [
                'buildArgs' => ['valor' => '0.00'],
                'violacaoEsperada' => LivroSemValorException::class,
            ],

            'valor negativo' => [
                'buildArgs' => ['valor' => '-1.00'],
                'violacaoEsperada' => LivroSemValorException::class,
            ],
            'sem autor'            => [
                'buildArgs'         => ['comAutor' => false],
                'violacaoEsperada'  => LivroSemAutorException::class,
            ],
            'sem assunto'          => [
                'buildArgs'         => ['comAssunto' => false],
                'violacaoEsperada'  => LivroSemAssuntoException::class,
            ],
        ];
    }

    /**
     * @param LivroBuildArgs $buildArgs
     */
    #[Test]
    #[DataProvider('violacoesIndividuaisProvider')]
    public function criarLancaLivroInvalidoComViolacaoEsperada(
        array $buildArgs,
        string $violacaoEsperada
    ): void {
        $livro = $this->buildLivroValido(...$buildArgs);

        $this->repositoryMock->expects($this->never())->method('save');

        try {
            $this->service->criar($livro);
            $this->fail('LivroInvalidoException não foi lançada.');
        } catch (LivroInvalidoException $e) {
            $tiposDeViolacao = array_map(
                static fn(object $v) => $v::class,
                $e->getViolacoes()
            );

            $this->assertContains(
                $violacaoEsperada,
                $tiposDeViolacao,
                sprintf(
                    'Esperava a violação %s, mas obteve: %s',
                    $violacaoEsperada,
                    implode(', ', $tiposDeViolacao)
                )
            );
        }
    }

    /**
     * @param LivroBuildArgs $buildArgs
     */
    #[Test]
    #[DataProvider('violacoesIndividuaisProvider')]
    public function atualizarLancaLivroInvalidoComViolacaoEsperada(
        array $buildArgs,
        string $violacaoEsperada
    ): void {
        $livro = $this->buildLivroValido(...$buildArgs);

        $this->repositoryMock->expects($this->never())->method('save');

        try {
            $this->service->atualizar($livro);
            $this->fail('LivroInvalidoException não foi lançada.');
        } catch (LivroInvalidoException $e) {
            $tiposDeViolacao = array_map(
                static fn(object $v) => $v::class,
                $e->getViolacoes()
            );

            $this->assertContains($violacaoEsperada, $tiposDeViolacao);
        }
    }

    // =========================================================================
    // validarLivro — múltiplas violações simultâneas
    // =========================================================================

    #[Test]
    public function criarAgregaMultiplasViolacoes(): void
    {
        $livro = $this->buildLivroValido(
            titulo:   '',
            editora:  '',
            comAutor: false,
        );

        $this->repositoryMock->expects($this->never())->method('save');

        try {
            $this->service->criar($livro);
            $this->fail('LivroInvalidoException não foi lançada.');
        } catch (LivroInvalidoException $e) {
            $this->assertGreaterThanOrEqual(3, count($e->getViolacoes()));
        }
    }

    // =========================================================================
    // remover
    // =========================================================================

    #[Test]
    public function removerDelegaAoRepositorio(): void
    {
        $livro = $this->createMock(Livro::class);

        $this->repositoryMock
            ->expects($this->once())
            ->method('remove')
            ->with($livro, true);

        $this->service->remover($livro);
    }

    // =========================================================================
    // listarPorAutor / listarPorAssunto
    // =========================================================================

    /** @return array<string, array{codau: int, quantidadeLivros: int}> */
    public static function listarPorAutorProvider(): array
    {
        return [
            'autor com dois livros' => ['codau' => 1,  'quantidadeLivros' => 2],
            'autor sem livros'      => ['codau' => 99, 'quantidadeLivros' => 0],
        ];
    }

    #[Test]
    #[DataProvider('listarPorAutorProvider')]
    public function listarPorAutorRetornaLivrosDoRepositorio(
        int $codau,
        int $quantidadeLivros
    ): void {
        $livrosMock = array_fill(0, $quantidadeLivros, $this->createMock(Livro::class));

        $this->repositoryMock
            ->expects($this->once())
            ->method('findByAutor')
            ->with($codau)
            ->willReturn($livrosMock);

        $result = $this->service->listarPorAutor($codau);

        $this->assertCount($quantidadeLivros, $result);
    }

    /** @return array<string, array{codas: int, quantidadeLivros: int}> */
    public static function listarPorAssuntoProvider(): array
    {
        return [
            'assunto com três livros' => ['codas' => 5,   'quantidadeLivros' => 3],
            'assunto sem livros'      => ['codas' => 100, 'quantidadeLivros' => 0],
        ];
    }

    #[Test]
    #[DataProvider('listarPorAssuntoProvider')]
    public function listarPorAssuntoRetornaLivrosDoRepositorio(
        int $codas,
        int $quantidadeLivros
    ): void {
        $livrosMock = array_fill(0, $quantidadeLivros, $this->createMock(Livro::class));

        $this->repositoryMock
            ->expects($this->once())
            ->method('findByAssunto')
            ->with($codas)
            ->willReturn($livrosMock);

        $result = $this->service->listarPorAssunto($codas);

        $this->assertCount($quantidadeLivros, $result);
    }

    // =========================================================================
    // Limites de fronteira — ano de publicação
    // =========================================================================

    /** @return array<string, array{ano: int, devePassar: bool}> */
    public static function anoPublicacaoFronteiraProvider(): array
    {
        $anoAtual = (int) date('Y');

        return [
            'ano atual (válido)'       => ['ano' => $anoAtual,     'devePassar' => true],
            'ano anterior (válido)'    => ['ano' => $anoAtual - 1, 'devePassar' => true],
            'próximo ano (inválido)'   => ['ano' => $anoAtual + 1, 'devePassar' => false],
        ];
    }

    #[Test]
    #[DataProvider('anoPublicacaoFronteiraProvider')]
    public function criarValidaFronteiraDoAnoDePublicacao(int $ano, bool $devePassar): void
    {
        $livro = $this->buildLivroValido(anoPublicacao: (string) $ano);

        if ($devePassar) {
            $this->repositoryMock->expects($this->once())->method('save');
            $this->service->criar($livro);
        } else {
            $this->repositoryMock->expects($this->never())->method('save');
            $this->expectException(LivroInvalidoException::class);
            $this->service->criar($livro);
        }
    }
}