<?php

namespace App\Controller;

use App\Entity\Store;
use App\Entity\User;
use App\Form\StoreType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class StoreController extends AbstractController
{
    #[Route('/store', name: 'app_store', methods: ['GET', 'POST'])]
   public function newStore(Request $request, EntityManagerInterface $entityManager, #[CurrentUser] User $user): Response
    {
        $store = new Store();

        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $store->setCreatedAt(new \DateTimeImmutable());
            $store->setCreatedBy($user);

            $entityManager->persist($store);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('store/index.html.twig', ['form' => $form]);
    }
}
