<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Violation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateTerminationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason')
            ->add('notes')
            ->add('valid_until')
            ->add('recipient', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'placeholder' => 'Select a user',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Violation::class,
        ]);
    }
}
