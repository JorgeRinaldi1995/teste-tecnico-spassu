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

    public function save(Assunto $assunto, bool $flush = true): void
    {
        $this->getEntityManager()->persist($assunto);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Assunto $assunto, bool $flush = true): void
    {
        $this->getEntityManager()->remove($assunto);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}