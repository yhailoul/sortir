<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'placeholder' => 'Nom :'
            ])
            ->add('dateStartHour', DateTimeType::class, [
                'label' => "Date de début de l'évennement :",
                'widget' => 'single_text'
            ])
            ->add('dateLimitInscription', DateTimeType::class, [
                'label' => 'Date limite inscription :'
            ])

            ->add('nbInscriptionMax', IntegerType::class, [
                'placeholder' => 'Taille limite de participant :'
            ])
            ->add('infosEvent', TextareaType::class, [
                'placeholder' => 'Description :'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class
        ]);
    }
}
