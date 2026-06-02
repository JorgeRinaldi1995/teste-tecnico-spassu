<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AssuntoRepository;

#[ORM\Entity(repositoryClass: AssuntoRepository::class)]
#[ORM\Table(name: 'assunto')]
class Assunto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'codas')]
    private ?int $codas = null;

    #[ORM\Column(length: 40)]
    private ?string $descricao = null;

    #[ORM\ManyToMany(
        targetEntity: Livro::class,
        mappedBy: 'assuntos'
    )]
    private Collection $livros;

    public function __construct()
    {
        $this->livros = new ArrayCollection();
    }

        public function getCodas(): ?int
    {
        return $this->codas;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getLivros(): Collection
    {
        return $this->livros;
    }

    public function __toString(): string
    {
        return $this->descricao;
    }
}