<?php

namespace App\Form;

use App\Entity\Events; // Changed from Aziz to Events
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class EventsType extends AbstractType // Changed class name from AzizType to EventsType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => true,
                'attr' => ['maxlength' => 255],
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'attr' => ['maxlength' => 1000],
            ])
            ->add('lieu', TextType::class, [
                'required' => true,
                'attr' => ['maxlength' => 255],
            ])
            ->add('date_debut', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'data' => new \DateTime(),
            ])
            ->add('date_fin', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'data' => new \DateTime('+1 day'),
            ])
            ->add('prix', NumberType::class, [
                'required' => true,
                'attr' => ['min' => 0],
            ])
            ->add('film', TextType::class, [
                'required' => true,
                'attr' => ['maxlength' => 255],
            ])
            ->add('typeevent', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Camping' => 'camping',
                    'Randonnée' => 'randonnée',
                    'Safari'=>'safari',
                    
                ],
                'expanded' => false,
                'attr' => ['maxlength' => 255],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (JPG, JPEG, PNG, GIF)',
                'mapped' => false, // Ne pas lier directement à l'entité
                'required' => false, // Facultatif
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'], // Ajout de 'image/jpg'
                        'mimeTypesMessage' => 'Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.',
                    ])
                ],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class, // Changed from Aziz to Events
        ]);
    }
}
