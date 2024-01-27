<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\MessageRequest;
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

    #[Route('/chats/{chatUid}', name: 'app_chat_view')]
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

    #[Route('/requests', name: 'app_chat_requests')]
    public function requests(EntityManagerInterface $entityManager, Security $security): Response
    {
        $currentUser = $security->getUser();
        $requests = $entityManager->getRepository(MessageRequest::class)->findBy([
            "recipient" => $currentUser,
            "accepted" => false,
        ]);

        return $this->render('chat/requests.html.twig', [
            "requests" => $requests
        ]);
    }

    #[Route('/requests/{requestUid}/accept', name: 'app_chat_requests_accept')]
    public function acceptRequest(string $requestUid, EntityManagerInterface $entityManager, Security $security): Response
    {
        $currentUser = $security->getUser();
        $request = $entityManager->getRepository(MessageRequest::class)->findOneBy([
            "id" => $requestUid,
            "recipient" => $currentUser,
            "accepted" => false,
        ]);

        if (!$request) {
            return $this->redirectToRoute('app_chat_requests');
        }

        $request->setAccepted(true);
        $entityManager->flush();

        $existingChat = $entityManager->getRepository(Chat::class)->findOneBy([
            "creator" => $currentUser,
            "receipient" => $request->getCreator()
        ]);
        if (!$existingChat) {
            $existingChat = $entityManager->getRepository(Chat::class)->findOneBy([
                "creator" => $request->getCreator(),
                "receipient" => $currentUser
            ]);
        }

        if ($existingChat) {
            return $this->redirectToRoute('app_chat_view', [
                "chatUid" => $existingChat->getChatId()
            ]);
        }

        $randomChatId = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 24);

        $chat = new Chat();
        $chat->setChatId($randomChatId);
        $chat->setCreator($currentUser);
        $chat->setReceipient($request->getCreator());
        $chat->setCreatedAt(new \DateTimeImmutable());
        $chat->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($chat);
        $entityManager->flush();


        return $this->redirectToRoute('app_chat_view', [
            "chatUid" => $chat->getChatId()
        ]);
    }

    #[Route('/requests/{requestUid}/decline', name: 'app_chat_requests_decline')]
    public function declineRequest(string $requestUid, EntityManagerInterface $entityManager, Security $security): Response
    {
        $currentUser = $security->getUser();
        $request = $entityManager->getRepository(MessageRequest::class)->findOneBy([
            "id" => $requestUid,
            "recipient" => $currentUser,
            "accepted" => false,
        ]);

        if (!$request) {
            return $this->redirectToRoute('app_chat_requests');
        }

        $entityManager->remove($request);
        $entityManager->flush();

        return $this->redirectToRoute('app_chat_requests');
    }

    #[Route('/requests/send', name: 'app_chat_requests_send')]
    public function sendRequest(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->redirectToRoute('app_chat_requests');
        }

        $currentUser = $security->getUser();

        $recipient = $entityManager->getRepository(User::class)->findOneBy([
            "username" => $request->request->get('userQuery')
        ]);
        if (!$recipient) {
            $recipient = $entityManager->getRepository(User::class)->findOneBy([
                "email" => $request->request->get('userQuery')
            ]);
        }

        if (!$recipient) {
            return $this->redirectToRoute('app_chat_requests');
        }

        if ($recipient === $currentUser) {
            return $this->redirectToRoute('app_chat_requests');
        }

        $existingRequest = $entityManager->getRepository(MessageRequest::class)->findOneBy([
            "creator" => $currentUser,
            "recipient" => $recipient,
            "accepted" => false,
        ]);

        if ($existingRequest) {
            return $this->redirectToRoute('app_chat_requests');
        }

        $existingChat = $entityManager->getRepository(Chat::class)->findOneBy([
            "creator" => $currentUser,
            "receipient" => $recipient
        ]);
        if (!$existingChat) {
            $existingChat = $entityManager->getRepository(Chat::class)->findOneBy([
                "creator" => $recipient,
                "receipient" => $currentUser
            ]);
        }

        if ($existingChat) {
            return $this->redirectToRoute('app_chat_view', [
                "chatUid" => $existingChat->getChatId()
            ]);
        }

        $request = new MessageRequest();
        $request->setCreator($currentUser);
        $request->setRecipient($recipient);
        $request->setAccepted(false);
        $request->setCreatedAt(new \DateTimeImmutable());
        $request->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($request);
        $entityManager->flush();

        return $this->redirectToRoute('app_chat_requests');
    }
}