<?php

namespace App\Repository;

use App\Entity\Assunto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AssuntoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assunto::class);
    }

    public function findWithLivros(int $id): ?Assunto
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.livros', 'l')
            ->leftJoin('l.autores', 'a')
            ->addSelect('l', 'a')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAssuntosAtivos(): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.livros', 'l')
            ->addSelect('l')
            ->groupBy('s.id')
            ->orderBy('s.descricao', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Assunto $assunto, bool $flush = false): void
    {
        $this->getEntityManager()->persist($assunto);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Assunto $assunto, bool $flush = false): void
    {
        $this->getEntityManager()->remove($assunto);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}