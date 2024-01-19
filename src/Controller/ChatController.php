<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
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
    public function view(string $chatUid, Security $security, Request $request, EntityManagerInterface $entityManager): Response
    {
        $chat = $entityManager->getRepository(Chat::class)->findOneBy([
            "chatId" => $chatUid
        ]);

        if (!$chat) {
            return $this->redirectToRoute('app_chat_index');
        }

        $currentUser = $security->getUser();
        if ($chat->getCreator() !== $currentUser && $chat->getReceipient() !== $currentUser) {
            return $this->redirectToRoute('app_chat_index');
        }

        $chattingTo = $chat->getCreator() === $currentUser ? $chat->getReceipient() : $chat->getCreator();

        $unreadMessages = $entityManager->getRepository(ChatMessage::class)->findBy(['chat' => $chat, 'seen' => false, 'creator' => $chattingTo]);
        foreach ($unreadMessages as $message) {
            $message->setSeen(true);
        }
        $entityManager->flush();

        return $this->render('chat/view.html.twig', [
            "chatUid" => $chatUid,
            "chat" => $chat
        ]);
    }
}