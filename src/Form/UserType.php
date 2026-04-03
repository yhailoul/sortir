<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $placeholder = $options['is_edit']
            ? 'Leave blank to keep current password'
            : 'Enter a password';

        $builder
            ->add('username', TextType::class, [
                'label' => 'Username :',
                'attr' => [
                    'placeholder' => 'Username',
                    'maxlength' => 180,
                    'pattern' => '.{3,}',
                ],
                'constraints' => [
                    new NotBlank(message: 'Username is required.'),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Firstname :',
                'attr' => [
                    'placeholder' => 'Firstname',
                ],
                'constraints' => [
                    new NotBlank(message: 'Firstname is required.'),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Lastname :',
                'attr' => [
                    'placeholder' => 'Lastname',
                ],
                'constraints' => [
                    new NotBlank(message: 'Lastname is required.'),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone :',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Phone number',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email :',
                'attr' => [
                    'placeholder' => 'Email',
                ],
                'constraints' => [
                    new NotBlank(message: 'Email is required.'),
                ],
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus :',
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
                    'label' => 'Password :',
                    'attr' => ['placeholder' => $placeholder],
                    'constraints' => [
                        new NotBlank(message: 'Password is required.', groups: ['manual_creation']),
                    ],
                ],
                'second_options' => ['label' => 'Repeat Password :'],
                'constraints' => [
                    new NotBlank(message: 'Repeat password is required.', groups: ['manual_creation']),
                    new Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'The password must be at least 8 characters long'
                    ),
                    new Regex(pattern: '/^(?=.*[a-z])/', message: 'Your password must contain at least one lowercase letter'),
                    new Regex(pattern: '/^(?=.*[A-Z])/', message: 'Your password must contain at least one uppercase letter'),
                    new Regex(pattern: '/^(?=.*\d)/', message: 'Your password must contain at least one number'),
                    new Regex(pattern: '/^(?=.*[@$!%*?&])/', message: 'Your password must contain at least one special character (@$!%*?&)'),
                ],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Profile Picture :',
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
            ]);

        if ($options['show_csv_import']) {
            $builder->add('csvFile', FileType::class, [
                'label' => 'Import via CSV',
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
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['Default'],
            'show_csv_import' => false,
            'is_edit' => false,
        ]);
    }
}
