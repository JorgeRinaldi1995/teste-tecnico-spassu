<?php

namespace App\Repository;

use App\Entity\Autor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Psr\Log\LoggerInterface;

class AutorRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly LoggerInterface $logger
    ){
        parent::__construct($registry, Autor::class);
    }

    /**
     * Busca um Autor pelo código primário com livros e assuntos carregados.
     *
     * @throws \RuntimeException em falha de infraestrutura
     * @throws \InvalidArgumentException se $codau for inválido
     */
    public function findWithLivros(int $codau): ?Autor
    {
        if ($codau <= 0) {
            throw new \InvalidArgumentException(
                "O código do autor deve ser um inteiro positivo, '{$codau}' fornecido."
            );
        }

        try {
            return $this->createQueryBuilder('a')
                ->leftJoin('a.livros', 'l')
                ->leftJoin('l.assuntos', 's')
                ->addSelect('l', 's')
                ->where('a.codau = :codau')
                ->setParameter('codau', $codau)
                ->getQuery()
                ->getOneOrNullResult();

        } catch (NonUniqueResultException $e) {
            $this->logger->error('Resultado não único ao buscar autor por codl.', [
                'codau'      => $codau,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException("Inconsistência de dados: mais de um autor encontrado para o código {$codl}.", 0, $e);
        } catch (ORMException $e) {
            $this->logger->error('Erro de ORM ao buscar autor com relações.', [
                'codau'      => $codau,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Erro ao consultar o autor. Tente novamente mais tarde.', 0, $e);
        }
    }

    /**
     * Persiste um Autor no EntityManager e, opcionalmente, executa o flush.
     *
     * A validação de regra de negócio (ex.: "autor deve pertencer a livro") deve ser
     * feita na camada de serviço/use-case antes de chamar este método.
     * O repositório é responsável apenas pela persistência.
     *
     * @throws \RuntimeException em falha de persistência ou violação de constraint
     */
    public function save(Autor $autor, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->persist($autor);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (OptimisticLockException $e) {
            $this->logger->error('Conflito de concorrência ao salvar autor.', [
                'autor_id'  => $autor->getCodau(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'O autor foi modificado por outro processo. Recarregue e tente novamente.', 0, $e
            );
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->error('Violação de unicidade ao salvar autor.', [
                'autor_id'  => $autor->getCodau(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Já existe um autor cadastrado com esses dados.', 0, $e
            );
        } catch (ORMException $e) {
            $this->logger->error('Erro de ORM ao salvar autor.', [
                'autor_id'  => $autor->getCodau(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Erro ao salvar o autor. Tente novamente mais tarde.', 0, $e
            );
        }
    }

    /**
     * Remove um Autor do EntityManager e, opcionalmente, executa o flush.
     *
     * @throws \RuntimeException em falha de remoção ou violação de integridade referencial
     */
    public function remove(Autor $autor, bool $flush = false): void
    {
        try {
            $this->getEntityManager()->remove($autor);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->logger->error('Violação de chave estrangeira ao remover autor.', [
                'autor_id'  => $autor->getCodau(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Não é possível remover o autor pois ele está vinculado a outros registros.', 0, $e
            );
        } catch (ORMException $e) {
            $this->logger->error('Erro ORM ao remover autor.', [
                'autor_id'  => $autor->getCodau(),
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Erro ao remover o autor. Tente novamente mais tarde.', 0, $e
            );
        }
    }
}