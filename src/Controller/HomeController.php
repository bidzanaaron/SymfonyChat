<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_index')]
    public function index(EntityManagerInterface $entityManager, Security $security): Response
    {
        $chatMessageRepository = $entityManager->getRepository(ChatMessage::class);
        $chatRepository = $entityManager->getRepository(Chat::class);

        $currentUser = $security->getUser();

        $totalMessages = $chatMessageRepository->count(["creator" => $currentUser]);

        $chatCount = $chatRepository->count(["creator" => $currentUser]);
        $chatCount += $chatRepository->count(["receipient" => $currentUser]);

        $memberSince = $currentUser->getCreatedAt();
        $memberSince = $memberSince->diff(new DateTime());
        $memberSince = $memberSince->format("%a");

        return $this->render('home/index.html.twig', [
            'totalMessages' => $totalMessages,
            'chatCount' => $chatCount,
            'memberSince' => $memberSince,
        ]);
    }
}