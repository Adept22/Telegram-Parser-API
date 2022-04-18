<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\ChatAvailablePhone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatAvailablePhone|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatAvailablePhone|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatAvailablePhone[]    findAll()
 * @method ChatAvailablePhone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatAvailablePhoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatAvailablePhone::class);
    }

    // /**
    //  * @return ChatAvailablePhone[] Returns an array of ChatAvailablePhone objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ChatAvailablePhone
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
