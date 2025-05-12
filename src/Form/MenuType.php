<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Restaurant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('repas')
            ->add('prix', NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prix ne peut pas être vide.'
                    ]),
                    new Assert\Positive([
                        'message' => 'Le prix doit être un nombre positif.'
                    ]),
                ],
                'scale' => 2, // Pour autoriser les nombres avec deux décimales
                'attr' => [
                    'min' => 0,
                    'step' => '0.01', // Permet d'entrer des valeurs décimales
                ],
            ])
            ->add('restaurant', EntityType::class, [
                'class' => Restaurant::class,
                'choice_label' => 'nom_resto',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
