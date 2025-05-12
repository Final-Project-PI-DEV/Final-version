<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('lieu', TextType::class, [
            'label' => 'Ville',
            'required' => true,
            'attr' => [
                'placeholder' => 'Commencez à taper votre ville...',
                'autocomplete' => 'off',
                'list' => 'tunisia-cities'
            ]
        ])
         ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Enter titre',
                    'required' => true,
                    'empty_data' => '',
                    'attr' => [
                        'placeholder' => 'Title',
                        'autocomplete' => 'off'
                    ]
                ]
            )
            ->add('content', TextareaType::class, [
                'label' => 'Enter titre',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('image', FileType::class, [
                'label' => 'A Image',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/*'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier',
                'attr' => [
                    'class' => 'btn btn-primary mt-2'
                ]
                ]);
        
    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
