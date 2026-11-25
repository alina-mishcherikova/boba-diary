<?php

namespace App\Service;

use App\Entity\BubbleTea;
use App\Entity\Store;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CatalogSubmission
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public')] private string $publicDirectory,
    ) {
    }

    public function save(Store|BubbleTea $entry, ?UploadedFile $photo): void
    {
        $file = null;
        try {
            if (null !== $photo) {
                $name = bin2hex(random_bytes(16)).'.'.$photo->guessExtension();
                $file = $photo->move($this->publicDirectory.'/uploads/catalog', $name);
                $entry->setImageUrl('/uploads/catalog/'.$name);
            }

            $this->entityManager->persist($entry);
            $this->entityManager->flush();
        } catch (\Throwable $error) {
            if (null !== $file) {
                @unlink($file->getPathname());
            }

            throw $error;
        }
    }
}
