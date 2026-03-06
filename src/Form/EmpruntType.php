<?php

namespace App\Form;

use App\Entity\Emprunt;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmpruntType extends AbstractType
{
    // public function buildForm(FormBuilderInterface $builder, array $options): void
    // {
    //     $builder
    //         ->add('dateEmprunt', null, [
    //             'widget' => 'single_text',
    //         ])
    //         ->add('dateRetour', null, [
    //             'widget' => 'single_text',
    //         ])
    //         ->add('statut')
    //        ->add('lastName', TextType::class, [
    //         'label' => 'Nom',
    //         'mapped' => false,
    //     ])

        public function buildForm(FormBuilderInterface $builder, array $options): void
{
   
    if (!$options['is_logged_in']) {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
            ->add('lastName', TextType::class, ['mapped' => false])
            ->add('firstName', TextType::class, ['mapped' => false])
            ->add('email', EmailType::class, ['mapped' => false]);
            
    }
    
    $builder->add('phoneNumber', TelType::class, ['required' => false, 'label'=>'Téléphone']);
}

public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Emprunt::class,
        'is_logged_in' => false, 
    ]);
}
}