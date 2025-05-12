<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'First Name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Last Name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Phone Number',
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control']
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'attr' => ['class' => 'form-control']
            ])
           
            ->add('is_man', ChoiceType::class, [
                'label' => 'Gender',
                'choices' => [
                    'Homme' => true,  // Male
                    'Femme' => false, // Female
                ],
                'expanded' => true, // Affiche les boutons radio
                'multiple' => false, // Un seul choix possible
                'attr' => ['class' => 'form-check'], // Classe pour le style
            ])
            ->add('date_de_naissance', DateType::class, [
                'label' => 'Date of Birth',
                'widget' => 'single_text', // Affiche un champ de date
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])
            ->add('image', FileType::class, [
                'label' => 'Profile Picture',
                'mapped' => false, // Empêche Symfony de lier directement à l'entité
                'required' => false,
                'attr' => ['class' => 'form-control']
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
