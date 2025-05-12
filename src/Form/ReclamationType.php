<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use App\Entity\Reponse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
        ->add('title', TextType::class, [
            'label' => 'Titre', 
            'attr'  => [
                'placeholder' => 'Entrer le Titre '
            ]
        ])
            ->add('objet', ChoiceType::class, [
                'label' => 'Objet',
                'choices' => [
                    'Produit défectueux' => [
                        'Produit de mauvaise qualité' => 'Produit de mauvaise qualité',
                        'Produit ne fonctionne pas correctement' => 'Produit ne fonctionne pas correctement',
                        'Accessoires ou pièces manquants' => 'Accessoires ou pièces manquants',
                        'Autre Produit défectueux ...' => 'Autre Produit défectueux',
                    ],
                    'Service non conforme' => [
                        'Mauvaise gestion des réservations' => 'Mauvaise gestion des réservations',
                        'Sécurité et nuisances sur place' => 'Sécurité et nuisances sur place',
                        'Accueil et service client médiocre' => 'Accueil et service client médiocre',
                        'Autre Service non conforme ...' => 'Autre Service non conforme',
                    ],
                    'Problème technique' => [
                        'Bug sur le site web' => 'Bug sur le site web',
                        'Problème de connexion à mon compte' => 'Problème de connexion à mon compte',
                        'Erreur de paiement' => 'erreur_paiement',
                        'Message d\'erreur lors d\'une action' => 'Message d\'erreur lors d\'une action',
                        'Autre Problème technique ...' => 'Autre Problème technique',
                    ],
                    'Autre ...' => 'autre',
                ],
                'placeholder' => 'Sélectionnez un objet',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description', 
                'attr'  => [
                    'placeholder' => 'Entrer la Description',
                    'rows' => 3
                ]
            ])
            ->add('statut', HiddenType::class)
            ->add('reponse', HiddenType::class)
           /* ->add('user', HiddenType::class )*/
            ->add('Envoyer', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary mt-2'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
