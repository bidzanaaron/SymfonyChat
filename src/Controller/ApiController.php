<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Violation;
use App\Service\ChatService;
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

        $chatService = new ChatService($entityManager, $security);
        if ($chatService->isRateLimited()) {
            $violation = new Violation();
            $violation->setType('Warning');
            $violation->setIssuer(null);
            $violation->setRecipient($currentUser);
            $violation->setReason('Spamming');
            $violation->setNotes('');
            $violation->setStatus('Issued');
            $violation->setValidUntil(null);
            $violation->setCreatedAt(new DateTimeImmutable());
            $violation->setUpdatedAt(new DateTimeImmutable());

            $entityManager->persist($violation);
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
            'message' => $messageString,
            'creator' => $currentUser->getUsername(),
        ]);
    }

    #[Route('/api/auth/getInfo', name: 'api_auth_info', methods: ['POST'])]
    public function getUserDetails(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $currentUser = $security->getUser();
        if (!$currentUser) {
            return $this->json([
                'success' => false,
            ]);
        }

        $availableChats = [];

        $creatorChats = $entityManager->getRepository(Chat::class)->findBy([
            'creator' => $currentUser
        ]);
        $receipientChats = $entityManager->getRepository(Chat::class)->findBy([
            'receipient' => $currentUser
        ]);

        $chats = array_merge($creatorChats, $receipientChats);

        foreach ($chats as $chat) {
            $availableChats[] = $chat->getChatId();
        }

        return $this->json([
            'success' => true,
            'username' => $currentUser->getUsername(),
            'availableChats' => $availableChats,
        ]);
    }

    #[Route('/api/locale/change', name: 'api_locale_change', methods: ['POST'])]
    public function changeLocale(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        $locale = $request->get('locale');
        if (!$locale) {
            return $this->json([
                'success' => false,
            ]);
        }

        $currentUser = $security->getUser();
        if (!$currentUser) {
            return $this->json([
                'success' => false,
            ]);
        }

        if (!in_array($locale, ['en', 'ger', 'fr'])) {
            return $this->json([
                'success' => false,
            ]);
        }

        if ($currentUser->getLanguage() === $locale) {
            return $this->json([
                'success' => false,
            ]);
        }

        $currentUser->setLanguage($locale);
        $entityManager->persist($currentUser);
        $entityManager->flush();

        return $this->json([
            'success' => true,
        ]);
    }

    #[Route('/api/chat/getMessages/{chatId}/{lastMessageId}', name: 'api_chat_get_messages', methods: ['GET'])]
    public function getChatMessages(Request $request, EntityManagerInterface $entityManager, Security $security, string $chatId, string $lastMessageId): Response
    {
        $currentUser = $security->getUser();
        if (!$currentUser) {
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

        $messages = $entityManager->getRepository(ChatMessage::class)->createQueryBuilder('m')
            ->where('m.chat = :chat')
            ->andWhere('m.id < :lastMessageId')
            ->setParameter('chat', $chat)
            ->setParameter('lastMessageId', $lastMessageId)
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        $lastChatMessage = $entityManager->getRepository(ChatMessage::class)->findOneBy([
            'chat' => $chat
        ], ['id' => 'ASC']);

        $html = $this->render('api/chatMessages.html.twig', [
            'success' => true,
            'chatMessages' => array_reverse($messages),
        ])->getContent();

        return $this->json([
            'success' => true,
            'html' => $html,
            'lastMessage' => $lastChatMessage === $messages[count($messages) - 1],
        ]);
    }
}