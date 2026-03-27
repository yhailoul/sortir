<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Status;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Nom :'
                ]
            ])
            ->add('dateStartHour', DateTimeType::class, [
                'label' => "Date et heure de DEBUT de l'évènement :",
                'widget' => 'single_text'
            ])
            ->add('dateEndHour', DateTimeType::class, [
                'label' => "Date et heure de FIN de l'évènement :",
                'widget' => 'single_text'
            ])
            ->add('registrationDeadline', DateTimeType::class, [
                'label' => "Date limite d'inscription :",
                'widget' => 'single_text'
            ])
            ->add('nbMaxRegistrations', IntegerType::class, [
                'attr' => [
                    'placeholder' => 'Taille limite de participant :'
                ]
            ])
            ->add('infosEvent', TextareaType::class, [
                'attr' => [
                    'placeholder' => "Description de l'activité :"
                ]
            ])
            ->add('eventLocation', EntityType::class, [
                'class' => Location::class,
                'choice_label' => "name",
            ])
            ->add('eventStatus', EntityType::class, [
                'class' => Status::class,
                'choice_label' => "label",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class
        ]);
    }
}
