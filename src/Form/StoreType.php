<?php

namespace App\Form;

use App\Entity\Store;
use App\Entity\Currency;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('address')
            ->add('city')
            ->add('country')
            ->add('postal_code')
            ->add('currency', EntityType::class, [
                'class' => Currency::class,
                'choice_label' => 'code',
                'placeholder' => 'Choose a currency',
                'constraints' => [new Assert\NotNull()],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Store::class,
        ]);
    }
}
