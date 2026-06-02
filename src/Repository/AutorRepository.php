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

    public function save(Autor $autor, bool $flush = true): void
    {
        $this->getEntityManager()->persist($autor);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Autor $autor, bool $flush = true): void
    {
        $this->getEntityManager()->remove($autor);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}