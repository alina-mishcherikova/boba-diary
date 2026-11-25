<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class CatalogPhotoType extends AbstractType
{
    public function getParent(): string
    {
        return FileType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'required' => false,
            'attr' => ['accept' => 'image/jpeg,image/png,image/webp'],
            'constraints' => [new Assert\File(maxSize: '5M', extensions: ['jpg', 'jpeg', 'png', 'webp'])],
        ]);
    }
}
