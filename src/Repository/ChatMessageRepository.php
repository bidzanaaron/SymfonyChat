<?php

namespace App\Repository;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChatMessage>
 *
 * @method ChatMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatMessage[]    findAll()
 * @method ChatMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    public function getLastMessage(Chat $chat): ?ChatMessage
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.chat = :chatId')
            ->setParameter('chatId', $chat->getId())
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getUnreadMessagesCount(Chat $chat, User $chattingTo): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.chat = :chatId')
            ->andWhere('c.creator = :chattingTo')
            ->andWhere('c.seen = false')
            ->setParameter('chatId', $chat->getId())
            ->setParameter('chattingTo', $chattingTo->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

//    /**
//     * @return ChatMessage[] Returns an array of ChatMessage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ChatMessage
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
