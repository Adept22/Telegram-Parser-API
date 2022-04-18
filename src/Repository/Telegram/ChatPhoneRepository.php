<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\ChatPhone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatPhone|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatPhone|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatPhone[]    findAll()
 * @method ChatPhone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatPhoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatPhone::class);
    }

    // /**
    //  * @return ChatPhone[] Returns an array of ChatPhone objects
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
    public function findOneBySomeField($value): ?ChatPhone
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
