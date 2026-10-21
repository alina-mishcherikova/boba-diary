<?php

namespace App\Controller\Admin;

use App\Entity\BubbleTea;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

use Symfony\Component\Validator\Constraints as Assert;

class BubbleTeaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BubbleTea::class;
    }

    public function createEntity(string $entityFqcn): BubbleTea
    {
         $user = $this->getUser();
         if (!$user instanceof User || $user->getId() === null) {
        throw $this->createAccessDeniedException('Please log in.');
    }

        return (new BubbleTea())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setCreatedBy($user->getId());
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

        yield TextField::new('description')
            ->setRequired(false)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max: 255),
            ]);
        
        yield TextField::new('image_url')
            ->setRequired(false)
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(max: 255),
            ]);

        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('inactivated_at')->hideOnForm();
    
    }
    
}
