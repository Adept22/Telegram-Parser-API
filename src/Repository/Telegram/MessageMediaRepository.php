<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\MessageMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MessageMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageMedia[]    findAll()
 * @method MessageMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageMedia::class);
    }

    // /**
    //  * @return MessageMedia[] Returns an array of MessageMedia objects
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
    public function findOneBySomeField($value): ?MessageMedia
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
