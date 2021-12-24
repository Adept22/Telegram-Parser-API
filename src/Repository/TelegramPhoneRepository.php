<?php

namespace App\Repository;

use App\Entity\TelegramPhone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;

/**
 * @method TelegramPhone|null find($id, $lockMode = null, $lockVersion = null)
 * @method TelegramPhone|null findOneBy(array $criteria, array $orderBy = null)
 * @method TelegramPhone[]    findAll()
 * @method TelegramPhone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TelegramPhoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramPhone::class);
    }

    public function findAllOrderByTelegramChatsCount($limit = 50, $direction = 'ASC')
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.chats', 'c')
            ->orderBy('COUNT(t.id)', $direction)
            ->groupBy('t.id')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return TelegramPhone[] Returns an array of TelegramPhone objects
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
    public function findOneBySomeField($value): ?TelegramPhone
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
