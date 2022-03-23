<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\Chat;
use App\Entity\Telegram\ChatMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatMedia[]    findAll()
 * @method ChatMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMedia::class);
    }

    // /**
    //  * @return ChatMedia[] Returns an array of ChatMedia objects
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
    public function findOneBySomeField($value): ?ChatMedia
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
