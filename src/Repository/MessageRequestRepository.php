<?php

namespace App\Repository;

use App\Entity\MessageRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessageRequest>
 *
 * @method MessageRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageRequest[]    findAll()
 * @method MessageRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageRequest::class);
    }

//    /**
//     * @return MessageRequest[] Returns an array of MessageRequest objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MessageRequest
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
