<?php

namespace App\Controller;

use App\Entity\Store;
use App\Entity\StoreBubbleTea;
use App\Entity\User;
use App\Form\StoreMenuItemType;
use App\Repository\StoreBubbleTeaRepository;
use App\Repository\StoreRepository;
use App\Security\Voter\StoreVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_OWNER')]
final class LocationController extends AbstractController
{
    #[Route('/my-locations', name: 'app_my_locations', methods: ['GET'])]
    public function index(#[CurrentUser] User $user, StoreRepository $stores): Response
    {
        return $this->render('location/index.html.twig', [
            'stores' => $stores->findBy(['created_by' => $user], ['created_at' => 'DESC', 'id' => 'DESC']),
        ]);
    }

    #[Route('/my-locations/{id}/menu', name: 'app_location_menu', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(StoreVoter::MANAGE, subject: 'store')]
    public function menu(Store $store, Request $request, StoreBubbleTeaRepository $items, EntityManagerInterface $entityManager): Response
    {
        $item = (new StoreBubbleTea())->setStoreId($store)->setIsAvailable(true);
        $form = $this->createForm(StoreMenuItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $items->findOneBy(['store_id' => $store, 'bubbleTea' => $item->getBubbleTea()])) {
                $form->get('bubbleTea')->addError(new FormError('This drink is already on this menu.'));
            } else {
                $item->setCreatedAt(new \DateTimeImmutable());
                $entityManager->persist($item);
                $entityManager->flush();
                $this->addFlash('success', 'Drink added to your menu.');

                return $this->redirectToRoute('app_location_menu', ['id' => $store->getId()]);
            }
        }

        return $this->render('location/menu.html.twig', [
            'store' => $store,
            'items' => $items->findBy(['store_id' => $store], ['created_at' => 'DESC', 'id' => 'DESC']),
            'form' => $form,
        ]);
    }
}
