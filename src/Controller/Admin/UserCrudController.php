<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function createEntity(string $entityFqcn): User
    {
        return (new User())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setRoles(['ROLE_USER']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield EmailField::new('email')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Email(),
                new Assert\Length(max: 180),
            ]);

        yield TextField::new('username')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max: 255),
            ]);

        yield TextField::new('name')
            ->setRequired(true)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max: 255),
            ]);

        yield DateTimeField::new('createdAt')->hideOnForm();

        yield DateTimeField::new('cguValidatedAt', 'Terms accepted at')
            ->setRequired(true)
            ->setFormTypeOption('input', 'datetime_immutable')
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
            ]);

        if (Crud::PAGE_NEW === $pageName) {
            yield TextField::new('plainPassword', 'Password')
                ->setFormType(PasswordType::class)
                ->setRequired(true)
                ->setFormTypeOptions([
                    'mapped' => false,
                    'hash_property_path' => 'password',
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length(min: 6, max: 4096),
                    ],
                ]);
        }
    }
}
