<?php

namespace App\Entity;

use App\Repository\LivroRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LivroRepository::class)]
#[ORM\Table(name: 'livro')]
class Livro
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'codl', type: 'integer')]
    private ?int $codl = null;

    #[ORM\Column(length: 40)]
    private ?string $titulo = null;

    #[ORM\Column(length: 40)]
    private ?string $editora = null;

    #[ORM\Column(type: 'integer')]
    private ?int $edicao = null;

    #[ORM\Column(name: 'ano_publicacao', length: 4)]
    private ?string $anoPublicacao = null;

    public function getCodl(): ?int
    {
        return $this->codl;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getEditora(): ?string
    {
        return $this->editora;
    }

    public function setEditora(string $editora): self
    {
        $this->editora = $editora;
        return $this;
    }

    public function getEdicao(): ?int
    {
        return $this->edicao;
    }

    public function setEdicao(int $edicao): self
    {
        $this->edicao = $edicao;
        return $this;
    }

    public function getAnoPublicacao(): ?string
    {
        return $this->anoPublicacao;
    }

    public function setAnoPublicacao(string $anoPublicacao): self
    {
        $this->anoPublicacao = $anoPublicacao;
        return $this;
    }
}