<?php

namespace App\Form;


use App\Entity\Categories;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoriesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Nom'

            ])

            ->add('parent', EntityType::class,[
                'class'=>Categories::class,
                'choice_label'=>'name',
                'label'=>"Parent",
                'required' => false,
                'placeholder' => 'Aucun',
                'empty_data' => null,
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.parent IS NULL');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}