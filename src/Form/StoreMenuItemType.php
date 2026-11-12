<?php

namespace App\Form;

use App\Entity\BubbleTea;
use App\Entity\Currency;
use App\Entity\StoreBubbleTea;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class StoreMenuItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bubbleTea', EntityType::class, [
                'class' => BubbleTea::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a drink',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('price', MoneyType::class, [
                'currency' => false,
                'input' => 'string',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\PositiveOrZero(),
                    new Assert\LessThanOrEqual('99999999.99'),
                ],
            ])
            ->add('currency', EntityType::class, [
                'class' => Currency::class,
                'choice_label' => 'code',
                'placeholder' => 'Choose a currency',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('is_available', CheckboxType::class, [
                'label' => 'Available',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => StoreBubbleTea::class]);
    }
}
