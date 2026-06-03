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
            ->add('titulo', TextType::class, [
                'label' => 'Titulo',
                'required' => true,
                'attr' => [
                    'maxlength' => 40,
                ],
            ])
            ->add('editora', TextType::class, [
                'label' => 'Editora',
                'required' => true,
                'attr' => [
                    'maxlength' => 40,
                ],
            ])
            ->add('edicao', IntegerType::class, [
                'label' => 'Edição',
                'required' => true,
            ])
            ->add('anoPublicacao', TextType::class, [
                'label' => 'Ano Publicação',
                'required' => true,
                'attr' => [
                    'maxlength' => 4
                ]
            ])
            ->add('autores', EntityType::class, [
                'class' => Autor::class,
                'choice_label' => 'nome',
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'label' => 'Autores',
            ])
            ->add('assuntos', EntityType::class, [
                'class' => Assunto::class,
                'choice_label' => 'descricao',
                'required' => true,
                'multiple' => true,
                'expanded' => true,
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