<?php

namespace App\Repository;

use App\Entity\TelegramMessageMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TelegramMessageMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method TelegramMessageMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method TelegramMessageMedia[]    findAll()
 * @method TelegramMessageMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TelegramMessageMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramMessageMedia::class);
    }

    // /**
    //  * @return TelegramMessageMedia[] Returns an array of TelegramMessageMedia objects
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
    public function findOneBySomeField($value): ?TelegramMessageMedia
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
