<?php

namespace App\Form;

use App\Entity\City;
use Faker\Core\Number;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'enter a city name'
                ],
                'required' => true,
            ])
            ->add('zipCode', NumberType::class,[
                'label' => 'zip code',
                'attr' => [
                    'placeholder' => 'enter a city zip code'
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
