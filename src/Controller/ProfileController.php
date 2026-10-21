<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\StoreRepository;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index( #[CurrentUser] User $user,
    StoreRepository $storeRepository,): Response
    {
        $stores = $storeRepository->findBy(
        ['created_by' => $user],
        ['created_at' => 'DESC', 'id' => 'DESC']
    );


        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'bubbleTeas' => $user->getBubbleTeas(),
            'stores' => $stores,
        ]);
    }
}
