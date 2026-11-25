<?php

namespace App\Controller;

use App\Entity\Store;
use App\Repository\BubbleTeaRepository;
use App\Repository\DrinkLogRepository;
use App\Repository\StoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ExploreController extends AbstractController
{
    #[Route('/explore/{tab}', name: 'app_explore', defaults: ['tab' => 'stores'], requirements: ['tab' => 'stores|drinks'], methods: ['GET'])]
    public function index(string $tab, StoreRepository $stores, BubbleTeaRepository $drinks, DrinkLogRepository $logs): Response
    {
        $active = ['inactivated_at' => null];

        return $this->render('explore/index.html.twig', [
            'tab' => $tab,
            'stores' => 'stores' === $tab ? $stores->findBy($active, ['name' => 'ASC']) : [],
            'drinks' => 'drinks' === $tab ? $drinks->findBy($active, ['name' => 'ASC']) : [],
            'storeCount' => $stores->count($active),
            'drinkCount' => $drinks->count($active),

        ]);
    }

    #[Route('/explore/stores/{id}', name: 'app_explore_store', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function store(Store $store, DrinkLogRepository $logs): Response
    {
        if (null !== $store->getInactivatedAt()) {
            throw $this->createNotFoundException();
        }

        return $this->render('explore/store.html.twig', [
            'store' => $store,
            'ratings' => $logs->getAverageRatings($store->getId()),
        ]);
    }
}
