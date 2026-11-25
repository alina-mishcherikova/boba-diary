<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\DiaryHistory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DiaryController extends AbstractController
{
    #[Route('/diary', name: 'app_diary', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(#[CurrentUser] User $user, DiaryHistory $history): Response
    {
        return $this->render('diary/index.html.twig', [
            'months' => $history->forUser($user->getId()),
        ]);
    }
}
