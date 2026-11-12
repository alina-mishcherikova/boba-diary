<?php

namespace App\Controller\Admin;

use App\Entity\Store;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

use Symfony\Component\Validator\Constraints as Assert;

class StoreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Store::class;
    }

    public function createEntity(string $entityFqcn): Store
    {
         $user = $this->getUser();
         if (!$user instanceof User || $user->getId() === null) {
        throw $this->createAccessDeniedException('Please log in.');
    }

        return (new Store())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setCreatedBy($user);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield TextField::new('name')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max: 255),
            ]);
        yield TextField::new('address')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max: 255),
            ]);
        yield TextField::new('city')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max: 255),
            ]);
        yield TextField::new('country')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max: 255),
            ]);
        yield TextField::new('postal_code')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max:20),
            ]);
        
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield AssociationField::new('created_by', 'Created by')
            ->formatValue(static fn (?User $user): string => $user?->getUserIdentifier() ?? '')
            ->hideOnForm();
    }
}
