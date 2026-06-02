<?php

namespace App\Form;

use App\Entity\Autor;
use App\Entity\Livro;
use App\Entity\Assunto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class LivroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class)
            ->add('editora', TextType::class)
            ->add('edicao', IntegerType::class)
            ->add('anoPublicacao', TextType::class, [
                'attr' => [
                    'maxlength' => 4
                ]
            ])
            ->add('autores', EntityType::class, [
                'class' => Autor::class,
                'choice_label' => 'nome',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Autores',
            ])
            ->add('assuntos', EntityType::class, [
                'class' => Assunto::class,
                'choice_label' => 'descricao',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Assuntos',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livro::class,
        ]);
    }
}