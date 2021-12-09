<?php

namespace App\Repository;

use App\Entity\TelegramMemberMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TelegramMemberMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method TelegramMemberMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method TelegramMemberMedia[]    findAll()
 * @method TelegramMemberMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TelegramMemberMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramMemberMedia::class);
    }

    // /**
    //  * @return TelegramMemberMedia[] Returns an array of TelegramMemberMedia objects
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
    public function findOneBySomeField($value): ?TelegramMemberMedia
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
