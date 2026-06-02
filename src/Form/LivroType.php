<?php

namespace App\Form;

use App\Entity\Livro;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livro::class,
        ]);
    }
}