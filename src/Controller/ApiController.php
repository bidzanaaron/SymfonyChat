<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    #[Route('/api/chat/send/{chatId}', name: 'api_chat_send', methods: ['POST'])]
    public function sendChatMessage(Request $request, EntityManagerInterface $entityManager, Security $security, string $chatId): Response
    {
        $currentUser = $security->getUser();
        if (!$currentUser) {
            return $this->json([
                'success' => false,
            ]);
        }

        $messageString = $request->get('message');
        if (!$messageString) {
            return $this->json([
                'success' => false,
            ]);
        }

        $messageString = trim($messageString);
        $messageString = htmlspecialchars($messageString);
        if (!$messageString) {
            return $this->json([
                'success' => false,
            ]);
        }

        $chat = $entityManager->getRepository(Chat::class)->findOneBy([
            'chatId' => $chatId
        ]);

        if (!$chat) {
            return $this->json([
                'success' => false,
            ]);
        }

        if ($chat->getCreator() !== $currentUser && $chat->getReceipient() !== $currentUser) {
            return $this->json([
                'success' => false,
            ]);
        }

        $message = new ChatMessage();
        $message->setCreator($currentUser);
        $message->setChat($chat);
        $message->setContent($messageString);
        $message->setSeen(false);
        $message->setCreatedAt(new DateTimeImmutable());
        $message->setUpdatedAt(new DateTimeImmutable());

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->json([
            'success' => true,
        ]);
    }
}