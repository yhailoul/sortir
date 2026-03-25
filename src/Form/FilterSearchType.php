<?php

namespace App\Form;

use App\Form\Model\FilterSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('organized', CheckboxType::class, [
                'label' => 'Events I organized',
                'required' => false,

            ])
            ->add('signedUp', CheckboxType::class, [
                'label' => 'Events I signed-up for',
                'required' => false,

            ])
            ->add('passed', CheckboxType::class, [
                'label' => 'Passed events',
                'required' => false,

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FilterSearch::class,
            'required' => false,
        ]);
    }
}
