<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/chats', name: 'app_chat_index')]
    public function index(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        return $this->render('chat/index.html.twig');
    }

    #[Route('/chats/{chatUid}')]
    public function view(string $chatUid, Request $request, EntityManagerInterface $entityManager): Response
    {
        $chat = $entityManager->getRepository(Chat::class)->findOneBy([
            "chatId" => $chatUid
        ]);

        if (!$chat) {
            throw $this->createNotFoundException("Chat not found");
        }

        return $this->render('chat/view.html.twig', [
            "chatUid" => $chatUid,
            "chat" => $chat
        ]);
    }
}