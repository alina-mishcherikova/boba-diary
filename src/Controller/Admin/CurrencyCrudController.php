<?php

namespace App\Controller\Admin;

use App\Entity\Currency;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints as Assert;

class CurrencyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Currency::class;
    }

    public function createEntity(string $entityFqcn): Currency
    {
        return (new Currency())->setIsActive(true);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield TextField::new('code')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Regex(
                    pattern: '/\A[A-Z]{3}\z/',
                    message: 'Enter a three-letter uppercase currency code.',
                ),
            ]);

        yield TextField::new('name')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max: 50),
            ]);

        yield TextField::new('symbol')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max: 10),
            ]);

        yield BooleanField::new('is_active', 'Active')->renderAsSwitch(false);
    }
}
