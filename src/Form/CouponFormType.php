<?php

namespace App\Form;

use App\Entity\Coupons;
use App\Entity\CouponsTypes;
use App\Entity\Users;
//use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CouponFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Code'

            ])
            ->add('coupons_types', EntityType::class,[
                'class'=>CouponsTypes::class,
                'choice_label'=>'name',
                'label'=>"Type de coupons"
            ])

            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Description'
            ])

            ->add('discount', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Réduction'
            ])

            ->add('max_usage', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Maximum utilisation'
            ])

            ->add('validity',DateTimeType::class,[
                'attr' => [
                    'class' => 'datetime'
                ],
                'label' => 'Date de validité',
            ])

            ->add('is_valid', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Est valide',
                'required' => false,
            ]);

            //->add('save', SubmitType::class, ['label' => 'Enregistrer']);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coupons::class,
        ]);
    }
}