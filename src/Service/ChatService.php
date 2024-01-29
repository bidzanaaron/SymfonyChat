<?php

namespace App\Service;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ChatService
{
    protected EntityManagerInterface $entityManager;

    protected Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function loadSidebarData(): array
    {
        $creatorInstances = $this->entityManager->getRepository(Chat::class)->findBy([
            "creator" => $this->security->getUser()
        ]);
        $recipientInstances = $this->entityManager->getRepository(Chat::class)->findBy([
            "receipient" => $this->security->getUser()
        ]);

        $chatInstances = array_merge($creatorInstances, $recipientInstances);

        usort($chatInstances, static function ($a, $b) {
            return $a->getCreatedAt() <=> $b->getCreatedAt();
        });

        $chatMessageRepository = $this->entityManager->getRepository(ChatMessage::class);

        $chats = [];
        foreach ($chatInstances as $chatInstance) {
            $chats[] = [
                "chat" => $chatInstance,
                "chatId" => $chatInstance->getChatId(),
                "chattingTo" => $chatInstance->getCreator() === $this->security->getUser() ? $chatInstance->getReceipient() : $chatInstance->getCreator(),
                "lastMessage" => $chatMessageRepository->getLastMessage($chatInstance),
                "unreadMessages" => $chatMessageRepository->getUnreadMessagesCount($chatInstance, $chatInstance->getCreator() === $this->security->getUser() ? $chatInstance->getReceipient() : $chatInstance->getCreator()),
            ];
        }

        return $chats;
    }
}