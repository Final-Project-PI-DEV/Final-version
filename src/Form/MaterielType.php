<?php

namespace App\Form;

use App\Entity\Materiel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;

class MaterielType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du matériel'
            ])
            ->add('description', TextType::class, [
                'label' => 'Description'
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité totale',
                'html5' => true,
                'scale' => 0
            ])
            ->add('quantityAvailable', NumberType::class, [
                'label' => 'Quantité disponible',
                'html5' => true,
                'scale' => 0
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix',
                'html5' => true,
                'scale' => 2
            ])
            ->add('type', TextType::class, [
                'label' => 'Type de matériel'
            ])
            ->add('image', FileType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Seuls les JPG et PNG sont autorisés',
                    ])
                ]
            ]); // <-- Ajout de la fermeture correcte ici
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Materiel::class,
        ]);
    }
}
