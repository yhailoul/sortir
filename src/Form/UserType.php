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
use Symfony\Component\Validator\Constraints\File;

// ✅ contrainte de validation
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', options: [
                'constraints' => [
                    new NotBlank(message: 'Username is required.'),
                ],
            ])
            ->add('firstName', options: [
                'constraints' => [
                    new NotBlank(message: 'Firstname is required.'),
                ],
            ])
            ->add('lastName', options: [
                'constraints' => [
                    new NotBlank(message: 'Lastname is required.'),
                ],
            ])
            ->add('phone', options: [
                'required' => false,
            ])
            ->add('email', options: [
                'constraints' => [
                    new NotBlank(message: 'Email is required.'),
                ],
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(message: 'Campus is required.'),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'mapped' => false,
                'required' => false,
                'first_options' => [
                    'label' => 'Password',
                    'attr' => ['placeholder' => 'Leave blank to keep current password'],
                    'constraints' => [
                        new NotBlank(message: 'Password is required.'),
                    ],
                ],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Profile Picture',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                     new Image(
                        maxSize: '1M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        maxSizeMessage: 'Maximum file size allowed is 1MB',
                        mimeTypesMessage: 'Only JPG, PNG, webp files are allowed',
                    ),
                ],
            ])
            ->add('csvFile', FileType::class, [
                'label' => 'Importer via CSV',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        mimeTypes: ['text/csv', 'text/plain'],
                        mimeTypesMessage: 'The file must be a CSV file.',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['Default'],
        ]);
    }
}
