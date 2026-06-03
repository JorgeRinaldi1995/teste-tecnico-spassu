<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\AutorRepository;

#[ORM\Entity(repositoryClass: AutorRepository::class)]
#[ORM\Table(name: 'autor')]
class Autor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'codau')]
    private int $codau;

    #[Assert\NotBlank(message: 'O nome do autor é obrigatório.')]
    #[Assert\Length(
        max: 40,
        maxMessage: 'O nome deve ter no máximo {{ limit }} caracteres.'
    )]
    #[ORM\Column(length: 40)]
    private string $nome;

    #[ORM\ManyToMany(targetEntity: Livro::class, mappedBy: 'autores')]
    private Collection $livros;

    public function __construct()
    {
        $this->livros = new ArrayCollection();
    }

    public function getCodau(): int
    {
        return $this->codau;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;
        return $this;
    }

    public function getLivros(): Collection
    {
        return $this->livros;
    }

    public function __toString(): string
    {
        return $this->nome;
    }

}