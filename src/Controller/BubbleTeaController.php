<?php

namespace App\Controller;

use App\Entity\BubbleTea;
use App\Entity\User;
use App\Form\BubbleTeaType;
use App\Service\CatalogSubmission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class BubbleTeaController extends AbstractController
{
    #[Route('/bubble/tea', name: 'app_bubble_tea', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function newBubbleTea(Request $request, CatalogSubmission $submission, #[CurrentUser] User $user): Response
    {
        $bubbleTea = new BubbleTea();

        $form = $this->createForm(BubbleTeaType::class, $bubbleTea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bubbleTea->setCreatedAt(new \DateTimeImmutable());
            $bubbleTea->setCreatedBy($user);

            $submission->save($bubbleTea, $form->get('photo')->getData());

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('bubble_tea/index.html.twig', ['form' => $form]);
    }
}
