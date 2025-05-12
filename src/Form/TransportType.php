<?php

namespace App\Form;

use App\Entity\Events;
use App\Entity\Transport;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
class TransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('typetransport', ChoiceType::class, [
            'label' => 'Type de transport',
            'choices' => [
                '4*4' => '4*4',
                'Bus' => 'bus',
                'Mini bus' => 'mini-bus',  
                'Voiture' => 'voiture'
            ],
            'placeholder' => 'Choisir un type de transport',
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'attr' => [
                'class' => 'form-select',
                'data-style' => 'btn-primary'
            ]
        ])
        ->add('heure_depart', DateTimeType::class, [
            'label' => 'Heure de départ',
            'widget' => 'single_text',
            'html5' => true,
            'attr' => [
                'class' => 'form-control datetimepicker'
            ]
        ])
        ->add('heureArrive', DateTimeType::class, [
            'label' => 'Heure d\'arrivée',
            'widget' => 'single_text',
            'html5' => true,
            'attr' => [
                'class' => 'form-control datetimepicker'
            ]
        ])
        ->add('nbreescale', null, [
            'label' => 'Nombre d\'escales',
            'attr' => [
                'min' => 0,
                'class' => 'form-control'
            ]
        ])
        ->add('nbreplace', null, [
            'label' => 'Nombre de places',
            'attr' => [
                'min' => 1,
                'class' => 'form-control'
            ]
        ])
        ->add('matricule', null, [
            'label' => 'Matricule du véhicule',
            'attr' => [
                'class' => 'form-control',
               
            ]
        ])
        ->add('description', null, [
            'label' => 'Description du trajet',
            'attr' => [
                'rows' => 4,
                'class' => 'form-control'
            ]
        ])
        ->add('events', EntityType::class, [
            'class' => Events::class,
            'choice_label' => 'id',
            'label' => 'Événement associé',
            'placeholder' => 'Sélectionner un événement',
            'attr' => [
                'class' => 'form-select'
            ]
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transport::class,
        ]);
    }
}
