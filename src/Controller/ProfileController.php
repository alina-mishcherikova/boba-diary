<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DrinkLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(#[CurrentUser] User $user, DrinkLogRepository $drinkLogs): Response
    {
        $userId = $user->getId();

        return $this->render('profile/index.html.twig', [
            'bubbleTeas' => $user->getBubbleTeas(),
            'recentEntries' => $drinkLogs->findRecentForProfile($userId),
            'stats' => $drinkLogs->getProfileStats($userId),
        ]);
    }
}
