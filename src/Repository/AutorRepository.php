<?php

namespace App\Repository;

use App\Entity\Autor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AutorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Autor::class);
    }

    public function findWithLivros(int $id): ?Autor
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.livros', 'l')
            ->leftJoin('l.assuntos', 's')
            ->addSelect('l', 's')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findSemLivros(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.livros', 'l')
            ->where('l.id IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function save(Autor $autor, bool $flush = false): void
    {
        $this->getEntityManager()->persist($autor);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Autor $autor, bool $flush = false): void
    {
        $this->getEntityManager()->remove($autor);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}