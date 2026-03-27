<?php

namespace App\Form;

use App\Entity\Campus;
use App\Form\Model\FilterSearch;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('searchTerm', SearchType::class, [
                'label' => 'Search by name',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Search by name',
                    'class'=> 'form-control'
                ]
            ])
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
            ->add(
                'campus',EntityType::class,[
                'class' => Campus::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Choose a campus',
                'query_builder' => function (CampusRepository $campusRepository) {
                    return $campusRepository->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                }
            ])
            ->add('startDate', DateType::class,[
                'label' => 'From date',
                'required' => false,
                'widget' => 'single_text',
                //'format' => 'dd-MM-yyyy', ->cause des erreurs
                'attr' => [
                    'class' => 'form-control '
                ],
            ])
            ->add('endDate', DateType::class,[
                'label' => 'To date',
                'required' => false,
                'widget' => 'single_text',
                //'format' => 'dd-MM-yyyy', ->cause des erreurs
                'attr' => ['class' => 'form-control']
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
