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

    public function save(Livro $livro, bool $flush = true): void 
    {
        $this->getEntityManager()->persist($livro);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Livro $livro, bool $flush = true): void
    {
        $this->getEntityManager()->remove($livro);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAutores(): Collection
    {
        return $this->autores;
    }
}