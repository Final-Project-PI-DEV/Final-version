<?php

namespace App\Form;

use App\Entity\Restaurant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class RestaurantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_resto')
            ->add('specialite')
            ->add('description')
            ->add('email', EmailType::class)


            ->add('date_debut_colab', null, [
                'widget' => 'single_text',
            ])
            ->add('date_fin_colab', null, [
                'widget' => 'single_text',
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif',
                ],
                'constraints' => [
                    new Assert\Choice([
                        'choices' => ['actif', 'inactif'],
                        'message' => 'Le statut doit être "actif" ou "inactif".',
                    ]),
                ],
            ])


            ->add('image', FileType::class, [
                'label' => 'Image (JPEG, PNG)',

                'mapped' => false, // Nous ne voulons pas lier cela directement à l'entité Restaurant
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '5M', // Limiter la taille de l'image à 5 Mo
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image de type JPEG ou PNG.',
                    ]),
                ],
                'required' => false, // Le champ n'est pas obligatoire
            ])

            ->add('adresse', TextType::class, [ // Ajout du champ adresse
                'label' => 'Adresse',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'adresse ne peut pas être vide.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Restaurant::class,
        ]);
    }
}
