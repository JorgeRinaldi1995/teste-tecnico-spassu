<?php

namespace App\Repository;

use App\Entity\Assunto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Psr\Log\LoggerInterface;

class AssuntoRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly LoggerInterface $logger
    ){
        parent::__construct($registry, Assunto::class);
    }

    /**
     * Busca um assunto pelo código primário com autores e livros carregados.
     *
     * @throws \RuntimeException em falha de infraestrutura
     * @throws \InvalidArgumentException se $codl for inválido
     */
    public function findById(int $codas): ?Assunto
    {
        if ($codas <= 0) {
            throw new \InvalidArgumentException(
                "O código do assunto deve ser um inteiro positivo, '{$codas}' fornecido."
            );
        }

        try {
            return $this->createQueryBuilder('s')
                ->leftJoin('s.livros', 'l')
                ->leftJoin('l.autores', 'a')
                ->addSelect('l', 'a')
                ->where('s.codas = :codas')
                ->setParameter('codas', $codas)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $this->logger->error('Resultado não único ao buscar assunto por codas.', [
                'codas'      => $codas,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException("Inconsistência de dados: mais de um assunto encontrado para o código {$codas}.", 0, $e);
        }
    }

    /**
     * Retorna todos os assuntos relacionados a livros, ordenados por descricao.
     *
     * @return Assunto[]
     *
     * @throws \RuntimeException em falha de infraestrutura
     */
    public function findAllAssuntos(    
        int $limit = 20,
        int $offset = 0
    ): array {
        try {
            return $this->createQueryBuilder('s')
                ->leftJoin('s.livros', 'l')
                ->addSelect('l')
                ->orderBy('s.descricao', 'ASC')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();
        
        } catch (ORMException $e) {
            $this->logger->error('Erro ORM ao listar livros com relações.', [
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Erro ao listar os assuntos. Tente novamente mais tarde.', 0, $e);
        }
    }

    /**
     * Retorna todos os assuntos relacionados a livros, ordenados por descricao.
     *
     * @return Assunto[]
     *
     * @throws \RuntimeException em falha de infraestrutura
     */
    public function findAssuntosAtivos(): array
    {
        try {
            return $this->createQueryBuilder('s')
                ->innerJoin('s.livros', 'l')
                ->addSelect('l')
                ->groupBy('s.codas')
                ->orderBy('s.descricao', 'ASC')
                ->getQuery()
                ->getResult();
        
        } catch (ORMException $e) {
            $this->logger->error('Erro ORM ao listar livros com relações.', [
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Erro ao listar os assuntos. Tente novamente mais tarde.', 0, $e);
        }
    }

    /**
     * Persiste um Assunto no EntityManager e, opcionalmente, executa o flush.
     *
     * A validação de regra de negócio (ex.: "assunto deve pertencer a livro") deve ser
     * feita na camada de serviço/use-case antes de chamar este método.
     * O repositório é responsável apenas pela persistência.
     *
     * @throws \RuntimeException em falha de persistência ou violação de constraint
     */
    public function save(Assunto $assunto, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($assunto);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (OptimisticLockException $e) {
            $this->logger->error('Conflito de concorrência ao salvar assunto.', [
                'assunto_id'  => $assunto->getCodas(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'O assunto foi modificado por outro processo. Recarregue e tente novamente.', 0, $e
            );
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->error('Violação de unicidade ao salvar assunto.', [
                'assunto_id'  => $assunto->getCodas(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Já existe um assunto cadastrado com esses dados.', 0, $e
            );
        } catch (ORMException $e) {
            $this->logger->error('Erro de ORM ao salvar assunto.', [
                'assunto_id'  => $assunto->getCodas(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Erro ao salvar o assunto. Tente novamente mais tarde.', 0, $e
            );
        }
    }

    /**
     * Remove um Assunto do EntityManager e, opcionalmente, executa o flush.
     *
     * @throws \RuntimeException em falha de remoção ou violação de integridade referencial
     */
    public function remove(Assunto $assunto, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($assunto);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->logger->error('Violação de chave estrangeira ao remover assunto.', [
                'assunto_id'  => $assunto->getCodas(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Não é possível remover o assunto pois ele está vinculado a outros registros.', 0, $e
            );
        } catch (ORMException $e) {
            $this->logger->error('Erro ORM ao remover assunto.', [
                'assunto_id'  => $assunto->getCodas(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Erro ao remover o assunto. Tente novamente mais tarde.', 0, $e
            );
        }

    }
}