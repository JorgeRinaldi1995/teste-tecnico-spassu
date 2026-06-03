<?php

namespace App\Repository;

use App\Entity\Livro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LivroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livro::class);
    }

    public function findWithRelations(int $codl): ?livro
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.autores', 'a')
            ->leftJoin('l.assuntos', 's')
            ->addSelect('a', 's')
            ->where('l.codl = :codl')
            ->setParameter('codl', $codl)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.autores', 'a')
            ->leftJoin('l.assuntos', 's')
            ->addSelect('a', 's')
            ->orderBy('l.titulo', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAutor(int $codau): array
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.autores', 'a')
            ->addSelect('a')
            ->where('a.id = :codau')
            ->setParameter('codau', $codau)
            ->orderBy('l.titulo', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAssunto(int $codas): array
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.assuntos', 's')
            ->addSelect('s')
            ->where('s.id = :codas')
            ->setParameter('codas', $codas)
            ->orderBy('l.titulo', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Livro $livro, bool $flush = false): void
    {
        if ($livro->getAutores()->isEmpty()) {
            throw new \DomainException('Um livro deve ter ao menos um autor.');
        }

        $this->getEntityManager()->persist($livro);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Livro $livro, bool $flush = false): void
    {
        $this->getEntityManager()->remove($livro);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}