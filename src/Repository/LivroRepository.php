<?php

namespace App\Repository;

use App\Entity\Livro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Psr\Log\LoggerInterface;

class LivroRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly LoggerInterface $logger
    ){
        parent::__construct($registry, Livro::class);
    }

    /**
     * Busca um Livro pelo código primário com autores e assuntos carregados.
     *
     * @throws \RuntimeException em falha de infraestrutura
     * @throws \InvalidArgumentException se $codl for inválido
     */

    public function findWithRelations(int $codl): ?Livro
    {
        if ($codl <= 0) {
            throw new \InvalidArgumentException(
                "O código do livro deve ser um inteiro positivo, '{$codl}' fornecido."
            );
        }

        try {
            return $this->createQueryBuilder('l')
                ->leftJoin('l.autores', 'a')
                ->leftJoin('l.assuntos', 's')
                ->addSelect('a', 's')
                ->where('l.codl = :codl')
                ->setParameter('codl', $codl)
                ->getQuery()
                ->getOneOrNullResult();

        } catch (NonUniqueResultException $e) {
            $this->logger->error('Resultado não único ao buscar livro por codl.', [
                'codl'      => $codl,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException("Inconsistência de dados: mais de um livro encontrado para o código {$codl}.", 0, $e);
        } catch (ORMException $e) {
            $this->logger->error('Erro de ORM ao buscar livro com relações.', [
                'codl'      => $codl,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Erro ao consultar o livro. Tente novamente mais tarde.', 0, $e);
        }
    }

    /**
     * Retorna todos os livros com autores e assuntos carregados, ordenados por título.
     *
     * @return Livro[]
     *
     * @throws \RuntimeException em falha de infraestrutura
     */
    public function findAllWithRelations(): array
    {
        try {
            return $this->createQueryBuilder('l')
                ->leftJoin('l.autores', 'a')
                ->leftJoin('l.assuntos', 's')
                ->addSelect('a', 's')
                ->orderBy('l.titulo', 'ASC')
                ->getQuery()
                ->getResult();

        } catch (ORMException $e) {
            $this->logger->error('Erro ORM ao listar livros com relações.', [
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Erro ao listar os livros. Tente novamente mais tarde.', 0, $e);
        }
    }

    /**
     * Retorna livros de um autor específico.
     *
     * @return Livro[]
     *
     * @throws \InvalidArgumentException se $codau for inválido
     * @throws \RuntimeException         em falha de infraestrutura
     */
    public function findByAutor(int $codau): array
    {
        if ($codau <= 0) {
            throw new \InvalidArgumentException(
                "O código do autor deve ser um inteiro positivo, '{$codau}' fornecido."
            );
        }

        try {
            return $this->createQueryBuilder('l')
                ->innerJoin('l.autores', 'a')
                ->addSelect('a')
                ->where('a.codau = :codau')
                ->setParameter('codau', $codau)
                ->orderBy('l.titulo', 'ASC')
                ->getQuery()
                ->getResult();

        } catch (ORMException $e) {
            $this->logger->error('Erro ORM ao buscar livros por autor.', [
                'codau'     => $codau,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Erro ao consultar livros por autor. Tente novamente mais tarde.', 0, $e);
        }
    }

    /**
     * Retorna livros associados a um assunto específico.
     *
     * @return Livro[]
     *
     * @throws \InvalidArgumentException se $codas for inválido
     * @throws \RuntimeException         em falha de infraestrutura
     */
    public function findByAssunto(int $codas): array
    {
        if ($codas <= 0) {
            throw new \InvalidArgumentException(
                "O código do assunto deve ser um inteiro positivo, '{$codas}' fornecido."
            );
        }

        try {
            return $this->createQueryBuilder('l')
                ->innerJoin('l.assuntos', 's')
                ->addSelect('s')
                ->where('s.codas = :codas')
                ->setParameter('codas', $codas)
                ->orderBy('l.titulo', 'ASC')
                ->getQuery()
                ->getResult();
        } catch (ORMException $e) {
            $this->logger->error('Erro ORM ao buscar livros por assunto.', [
                'codas'     => $codas,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Erro ao consultar livros por assunto. Tente novamente mais tarde.', 0, $e);
        }
    }

    /**
     * Persiste um Livro no EntityManager e, opcionalmente, executa o flush.
     *
     * A validação de regra de negócio (ex.: "livro deve ter autor") deve ser
     * feita na camada de serviço/use-case antes de chamar este método.
     * O repositório é responsável apenas pela persistência.
     *
     * @throws \RuntimeException em falha de persistência ou violação de constraint
     */
    public function save(Livro $livro, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($livro);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (OptimisticLockException $e) {
            $this->logger->error('Conflito de concorrência ao salvar livro.', [
                'livro_id'  => $livro->getCodl(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'O livro foi modificado por outro processo. Recarregue e tente novamente.', 0, $e
            );
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->error('Violação de unicidade ao salvar livro.', [
                'livro_id'  => $livro->getCodl(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Já existe um livro cadastrado com esses dados.', 0, $e
            );
        } catch (ORMException $e) {
            $this->logger->error('Erro de ORM ao salvar livro.', [
                'livro_id'  => $livro->getCodl(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Erro ao salvar o livro. Tente novamente mais tarde.', 0, $e
            );
        }
    }

    /**
     * Remove um Livro do EntityManager e, opcionalmente, executa o flush.
     *
     * @throws \RuntimeException em falha de remoção ou violação de integridade referencial
     */
    public function remove(Livro $livro, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($livro);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->logger->error('Violação de chave estrangeira ao remover livro.', [
                'livro_id'  => $livro->getCodl(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Não é possível remover o livro pois ele está vinculado a outros registros.', 0, $e
            );
        } catch (ORMException $e) {
            $this->logger->error('Erro ORM ao remover livro.', [
                'livro_id'  => $livro->getCodl(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Erro ao remover o livro. Tente novamente mais tarde.', 0, $e
            );
        }
    }

}