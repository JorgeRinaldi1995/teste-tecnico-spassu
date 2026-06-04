<?php

namespace App\Entity;

use App\Entity\Autor;
use App\Repository\LivroRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

#[ORM\Entity(repositoryClass: LivroRepository::class)]
#[ORM\Table(name: 'livro')]
class Livro
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'codl', type: 'integer')]
    private int $codl;

    #[ORM\Column(length: 40)]
    private string $titulo;

    #[ORM\Column(length: 40)]
    private string $editora;

    #[ORM\Column(type: 'integer')]
    private int $edicao;

    #[ORM\Column(name: 'ano_publicacao', length: 4)]
    private string $anoPublicacao;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $valor;

    #[ORM\ManyToMany(targetEntity: Autor::class, inversedBy: 'livros', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'livro_autor')]
    #[ORM\JoinColumn(name: 'livro_codl', referencedColumnName: 'codl')]
    #[ORM\InverseJoinColumn(name: 'autor_codau', referencedColumnName: 'codau')]
    #[Assert\Count(min: 1, minMessage: 'Selecione pelo menos um autor.')]
    private Collection $autores;

    #[ORM\ManyToMany(targetEntity: Assunto::class, inversedBy: 'livros', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'livro_assunto')]
    #[ORM\JoinColumn(name: 'livro_codl', referencedColumnName: 'codl')]
    #[ORM\InverseJoinColumn(name: 'assunto_codas', referencedColumnName: 'codas')]
    #[Assert\Count(min: 1, minMessage: 'Selecione pelo menos um assunto.')]
    private Collection $assuntos;

    public function __construct()
    {
        $this->autores = new ArrayCollection();
        $this->assuntos = new ArrayCollection();
    }

    public function getCodl(): int
    {
        return $this->codl;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getEditora(): string
    {
        return $this->editora;
    }

    public function setEditora(string $editora): self
    {
        $this->editora = $editora;
        return $this;
    }

    public function getEdicao(): int
    {
        return $this->edicao;
    }

    public function setEdicao(int $edicao): self
    {
        $this->edicao = $edicao;
        return $this;
    }

    public function getAnoPublicacao(): string
    {
        return $this->anoPublicacao;
    }

    public function setAnoPublicacao(string $anoPublicacao): self
    {
        $this->anoPublicacao = $anoPublicacao;
        return $this;
    }

        public function getValor(): string
    {
        return $this->valor;
    }

    public function setValor(string $valor): self
    {
        $this->valor = $valor;
        return $this;
    }

    public function getAutores(): Collection
    {
        return $this->autores;
    }

    public function addAutor(Autor $autor): self
    {
        if (!$this->autores->contains($autor)) {
            $this->autores->add($autor);
        }

        return $this;
    }

    public function removeAutor(Autor $autor): self
    {
        $this->autores->removeElement($autor);

        return $this;
    }

    public function getAssuntos(): Collection
    {
        return $this->assuntos;
    }

    public function addAssunto(Assunto $assunto): self
    {
        if (!$this->assuntos->contains($assunto)) {
            $this->assuntos->add($assunto);
        }

        return $this;
    }

    public function removeAssunto(Assunto $assunto): self
    {
        $this->assuntos->removeElement($assunto);

        return $this;
    }
}