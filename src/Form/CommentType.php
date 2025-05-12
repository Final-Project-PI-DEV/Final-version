<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'required' => false, 
                'attr' => [
                    'placeholder' => 'Add a comment...',
                    'rows' => 1,
                    'class' => 'comment-input',
                    'required' => false
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'The comment cannot be empty.']),
                    new Assert\Length([
                        'max' => 500,
                        'maxMessage' => 'The comment cannot be longer than 500 characters.'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
