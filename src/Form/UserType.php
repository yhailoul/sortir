<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('firstName')
            ->add('lastName')
            ->add('phone')
            ->add('email')
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'mapped' => false,
                'required' => false,
                'first_options' => [
                    'label' => 'Password',
                    'attr' => ['placeholder' => 'Leave blank to keep current password'],
                ],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Profile Picture',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image(maxSize: '1M', mimeTypes: [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ], maxSizeMessage: 'Maximum file size allowed is 1MB', mimeTypesMessage: 'Only JPG, PNG, webp files are allowed')
                ]

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
